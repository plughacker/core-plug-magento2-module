<?php

namespace PlugHacker\PlugCore\Recurrence\Services;

use PlugHacker\PlugAPILib\Models\GetPlanItemResponse;
use PlugHacker\PlugAPILib\PlugAPIClient;
use PlugHacker\PlugCore\Kernel\Services\LogService;
use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;
use PlugHacker\PlugCore\Recurrence\Aggregates\Plan;
use PlugHacker\PlugCore\Recurrence\Factories\PlanFactory;
use PlugHacker\PlugCore\Recurrence\Repositories\PlanRepository;
use PlugHacker\PlugAPILib\Models\CreatePlanRequest;
use PlugHacker\PlugCore\Recurrence\ValueObjects\PlanId;
use PlugHacker\PlugCore\Recurrence\ValueObjects\PlanItemId;
use PlugHacker\PlugPagamentos\Concrete\Magento2CoreSetup;

class PlanService
{
    public function __construct()
    {
        Magento2CoreSetup::bootstrap();

        $config = Magento2CoreSetup::getModuleConfiguration();

        $secretKey = null;
        if ($config->getSecretKey() != null) {
            $secretKey = $config->getSecretKey()->getValue();
        }

        $password = '';

        \PlugHacker\PlugAPILib\Configuration::$basicAuthPassword = '';

        $this->plugApi = new PlugAPIClient($secretKey, $password);
    }

    /**
     * @param Plan $plan
     * @throws \PlugHacker\PlugCore\Kernel\Exceptions\InvalidParamException
     */
    public function save(Plan $plan)
    {
        $methodName = "createPlanAtPlug";
        if ($plan->getPlugId() !== null) {
            $methodName = "updatePlanAtPlug";
        }

        $result = $this->{$methodName}($plan);

        $planId = new PlanId($result->id);
        $plan->setPlugId($planId);

        $planRepository = new PlanRepository();
        $planRepository->save($plan);
    }

    public function createPlanAtPlug(Plan $plan)
    {
        $createPlanRequest = $plan->convertToSdkRequest();
        $planController = $this->plugApi->getPlans();

        try {
            $logService = $this->getLogService();
            $logService->info(
                'Create plan request: ' .
                json_encode($createPlanRequest, JSON_PRETTY_PRINT)
            );

            $result = $planController->createPlan($createPlanRequest);

            $logService->info(
                'Create plan response: ' .
                json_encode($result, JSON_PRETTY_PRINT)
            );

            $this->setItemsId($plan, $result);

            return $result;
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }


    }

    public function updatePlanAtPlug(Plan $plan)
    {
        $updatePlanRequest = $plan->convertToSdkRequest(true);
        $planController = $this->plugApi->getPlans();

        $this->updateItemsAtPlug($plan, $planController);
        $result = $planController->updatePlan($plan->getPlugId(), $updatePlanRequest);

        return $result;
    }

    protected function setItemsId(Plan $plan, $result)
    {
        $resultItems = $result->items;
        foreach ($resultItems as $resultItem) {
            $this->updateItems($plan, $resultItem);
        }
    }

    protected function updateItems(Plan $plan, GetPlanItemResponse $resultItem)
    {
        $planItems = $plan->getItems();
        foreach ($planItems as $planItem) {
            if ($this->isItemEqual($planItem, $resultItem)) {
                $planItem->setPlugId(
                  new PlanItemId($resultItem->id)
                );
            }
        }
    }

    protected function isItemEqual($planItem, $resultItem)
    {
        return $planItem->getName() == $resultItem->name;
    }

    protected function updateItemsAtPlug(Plan $plan, $planController)
    {
        foreach ($plan->getItems() as $item) {
            $planController->updatePlanItem(
                $plan->getPlugId(),
                $item->getPlugId(),
                $item->convertToSdkRequest()
            );
        }
    }

    public function findById($id)
    {
        $planRepository = $this->getPlanRepository();

        return $planRepository->find($id);
    }

    public function findByPlugId(AbstractValidString $plugId)
    {
        $planRepository = $this->getPlanRepository();

        return $planRepository->findByPlugId($plugId);
    }

    public function findAll()
    {
        $planRepository = $this->getPlanRepository();

        return $planRepository->listEntities(0, false);
    }

    public function findByProductId($id)
    {
        $planRepository = $this->getPlanRepository();

        return $planRepository->findByProductId($id);
    }

    public function delete($id)
    {
        $planRepository = $this->getPlanRepository();
        $plan = $planRepository->find($id);

        if (empty($plan)) {
            throw new \Exception("Plan not found - ID : {$id} ");
        }

        try {
            $planController = $this->plugApi->getPlans();
            $planController->deletePlan($plan->getPlugId());
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

        return $planRepository->delete($plan);
    }

    public function getPlanRepository()
    {
        return new PlanRepository();
    }

    public function getPlugAPILib($secretKey, $password)
    {
        return new PlugAPILib($secretKey, $password);
    }

    public function getLogService()
    {
        return new LogService(
            'PlanService',
            true
        );
    }
}

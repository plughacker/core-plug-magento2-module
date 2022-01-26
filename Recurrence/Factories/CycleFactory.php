<?php

namespace PlugHacker\PlugCore\Recurrence\Factories;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\Interfaces\FactoryInterface;
use PlugHacker\PlugCore\Recurrence\Aggregates\Cycle;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\CycleId;

class CycleFactory implements FactoryInterface
{
    private $cycle;

    public function __construct()
    {
        $this->cycle = new Cycle();
    }

    /**
     * @param array $postData
     * @return AbstractEntity|Cycle
     * @throws \PlugHacker\PlugCore\Kernel\Exceptions\InvalidParamException
     */
    public function createFromPostData($postData)
    {
        $this->cycle->setCycleId(new CycleId($postData['id']));
        $this->cycle->setCycleStart(new \DateTime($postData['start_at']));
        $this->cycle->setCycleEnd(new \DateTime($postData['end_at']));
        $this->setCycle($postData);

        return $this->cycle;
    }

    public function createFromDbData($dbData)
    {
        $cycle = new Cycle();

        $cycle->setCycleId(new CycleId($dbData['id']));
        $cycle->setCycleStart(new \DateTime($dbData['start_at']));
        $cycle->setCycleEnd(new \DateTime($dbData['end_at']));

        return $cycle;
    }

    public function setCycle($postData)
    {
        if (!empty($postData['cycle'])) {
            $this->cycle->setCycle($postData['cycle']);
        }
    }
}

<?php

namespace PlugHacker\PlugCore\Recurrence\Interfaces;

use PlugHacker\PlugCore\Recurrence\Aggregates\SubProduct;

interface SubProductEntityInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return \stdClass
     */
    public function convertToSdkRequest();

    /**
     * @return int
     */
    public function getProductId();

    /**
     * @param int $productId
     * @return SubProduct
     */
    public function setProductId($productId);

    /**
     * @return int
     */
    public function getCycles();

    /**
     * @param int $cycles
     * @return SubProduct
     */
    public function setCycles($cycles);

}

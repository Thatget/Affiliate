<?php

namespace MW\Affiliate\Model;

class Affiliatetransaction extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MW\Affiliate\Model\ResourceModel\Affiliatetransaction');
    }
}

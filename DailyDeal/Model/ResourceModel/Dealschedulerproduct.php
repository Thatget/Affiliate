<?php
namespace MW\DailyDeal\Model\ResourceModel;

class Dealschedulerproduct extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Class constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null                                  $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('mw_deal_scheduler_product', 'dealschedulerproduct_id');
    }
}

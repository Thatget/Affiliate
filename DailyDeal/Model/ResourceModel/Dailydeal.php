<?php
namespace MW\DailyDeal\Model\ResourceModel;

class Dailydeal extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
        $this->_init('mw_dailydeal', 'dailydeal_id');
    }
}

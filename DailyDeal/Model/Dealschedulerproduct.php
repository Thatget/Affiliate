<?php
namespace MW\DailyDeal\Model;

class Dealschedulerproduct extends \Magento\Framework\Model\AbstractModel
{
    protected $helperDailyDeal;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \MW\DailyDeal\Helper\Data $helperDailyDeal
     * @param Dealschedulerproduct $dealSchedulerProduct
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \MW\DailyDeal\Helper\Data $helperDailyDeal,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helperDailyDeal = $helperDailyDeal;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('MW\DailyDeal\Model\ResourceModel\Dealschedulerproduct');
        $this->setIdFieldName('dealschedulerproduct_id');
    }

    public function deleteAllByDealSchedulerId($deal_scheduler_id)
    {
        $collection = $this->getCollection()->addFieldToFilter('deal_scheduler_id', $deal_scheduler_id);
        foreach ($collection as $row) {
            $row->delete();
        }
    }

    /**
     * Return array 2D product of deal scheduler
     * @param $deal_scheduler_id
     * @return array
     */
    public function getProductOptionArray($deal_scheduler_id)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('deal_scheduler_id', $deal_scheduler_id)
            ->setOrder('deal_position', 'ASC');
        $data = [];
        foreach ($collection as $row) {
            $data[$row->getData('product_id')] = $row->getData();
        }
        return $data;
    }

    /**
     * Return array 2D product of deal scheduler order by 'generate_type'
     * @param array $data
     * @param $generate_type
     * @return array
     */
    public function sortProductOptionArray(
        $data = [],
        $generate_type = Status::DEAL_SCHEDULER_GENERATE_TYPE_ROTATORS
    ) {

        if ($generate_type == Status::DEAL_SCHEDULER_GENERATE_TYPE_RANDOMLY) {
            shuffle($data);
        } elseif ($generate_type == Status::DEAL_SCHEDULER_GENERATE_TYPE_ROTATORS) {
            $this->helperDailyDeal->reIndexArray($data);
        }
        return $data;
    }
}

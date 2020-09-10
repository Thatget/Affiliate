<?php
namespace MW\DailyDeal\Model\ResourceModel\Dailydeal;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'dailydeal_id';
    protected $eavConfig;
    protected $attribute;
    protected $eavAttribute;
    protected $helperDailyDeal;
    protected $storeManager;
    protected $date;

    /**
     * Collection constructor.
     *
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $attribute
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \MW\DailyDeal\Helper\Data $helperDailyDeal
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|NULL $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $attribute,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \MW\DailyDeal\Helper\Data $helperDailyDeal,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->attribute = $attribute;
        $this->eavAttribute = $eavAttribute;
        $this->helperDailyDeal = $helperDailyDeal;
        $this->date = $date;
        $this->storeManager = $storeManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(
            \MW\DailyDeal\Model\Dailydeal::class,
            \MW\DailyDeal\Model\ResourceModel\Dailydeal::class
        );
    }

    protected function _getAllIdsSelect($limit = null, $offset = null)
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $idsSelect->columns($this->getResource()->getIdFieldName(), 'main_table');
        $idsSelect->limit($limit, $offset);
        return $idsSelect;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getAllDeal()
    {
        $this->getSelect()->joinLeft(
            array( 'table_dealscheduler' => $this->getTable('mw_deal_scheduler')),
            'main_table.deal_scheduler_id = table_dealscheduler.deal_scheduler_id',
            array('table_dealscheduler.title')
        );

        return parent::_beforeLoad();
    }

    public function joinDate()
    {
//        $mainTable = $this->getMainTable();
//        $this->getSelect()->joinLeft(
//            ['scheduler'=>$this->getTable('mw_deal_scheduler')],
//            'mw_dailydeal.deal_scheduler_id = scheduler.deal_scheduler_id',
//            [
//                'mw_dailydeal.*'
//            ]
//        );
        $orderTable = $this->_resource->getTable('mw_deal_scheduler');
        $this->getSelect()
        ->join(
            ['ot'=>$orderTable],
            "main_table.deal_scheduler_id = ot.deal_scheduler_id",
            [
                'deal_data' => 'ot.*'
            ]
        );
        return $this;
    }

    /**     * Add scheduler to collection
     *
     * @return $this
     */
    public function _initSelect()
    {
        parent::_initSelect();
//        $this->getSelect()->joinLeft(
//            ['scheduler' => $this->getTable('mw_deal_scheduler')],
//            'main_table.deal_scheduler_id = scheduler.deal_scheduler_id AND scheduler.is_primary = 1',
//            ['*']
//        );
        return $this;
    }

    /**
     * Filter collection by specified website, customer group, coupon code, date.
     * Filter collection to use only active rules.
     * Involved sorting by sort_order column.
     *
     * @param int $dailydeal_id
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return $this
     */
    public function setValidationFilter($dailydeal_id)
    {
        $this->getSelect()->reset();
        parent::_initSelect();
        $this->addFieldToFilter('main_table.dailydeal_id', $dailydeal_id);
        return $this;
    }

    public function loadcurrentdeal($productid = null)
    {
        $deal = null;
        $store_id = $this->storeManager->getStore()->getId();
        $now = $this->date->date()->format('Y-m-d H:i:s');

        $this->addFieldToFilter('product_id', $productid)
            ->addFieldToFilter('status', \MW\DailyDeal\Model\Status::STATUS_TIME_RUNNING)
//            ->addFieldToFilter('expire', \MW\DailyDeal\Model\Status::STATUS_EXPIRE_FALSE)
//            ->addFieldToFilter('store_view', array(array('like'=>'%'. $store_id .'%'), array('like'=>'0')))
//            ->addFieldToFilter('start_date_time', array('to' => $now))
//            ->addFieldToFilter('end_date_time', array('from' => $now))
            ->load();

        if (count($this->getItems())) {
            $deal = $this->getFirstItem();
        }

        return $deal;
    }

    /**
     * Filter product status
     * @param $store_id
     * @return Collection
     */
    public function addProductStatusFilter($store_id)
    {
        $store_id_all = 0;
        $connection  = $this->_resource->getConnection();
        $code_id = $this->eavAttribute->getIdByCode('catalog_product', 'status');
        $catalog_product_entity_int = $connection->getTableName('catalog_product_entity_int');

        $this->getSelect()->joinInner(
            array( 'at_status_default' => $catalog_product_entity_int),
            "(main_table.product_id = at_status_default.entity_id) 
            AND (at_status_default.attribute_id = {$code_id}) 
            AND (at_status_default.store_id = {$store_id_all})",
            array('at_status.value')
        );
        $this->getSelect()->joinLeft(
            array( 'at_status' => $catalog_product_entity_int),
            "(main_table.product_id = at_status.entity_id) AND (at_status.attribute_id = {$code_id}) AND (at_status.store_id = {$store_id})",
            array('at_status.value')
        );

        $this->getSelect()->where(" (IF(at_status.value_id > 0, at_status.value, at_status_default.value) = '1')");

        return $this;
    }

    /**
     * @return $this
     */
    public function getConfigSortBy()
    {
        $sort = $this->helperDailyDeal->getConfigSortBy();
        if ($sort == \MW\DailyDeal\Model\System\Config\Sortby::SORT_BY_FEATURED_ENDDATETIME) {
            $this->addAttributeToSort('featured', 'ASC')
                ->addAttributeToSort('end_date_time', 'ASC');
        } elseif ($sort == \MW\DailyDeal\Model\System\Config\Sortby::SORT_BY_RANDOM) {
            $this->getSelect()->order('rand()');
        } elseif ($sort == \MW\DailyDeal\Model\System\Config\Sortby::SORT_BY_FEATURED_RANDOM) {
            $this->addFieldToFilter('featured', \MW\DailyDeal\Model\Status::STATUS_FEATURED_ENABLED);
            $this->getSelect()->order('rand()');
        }
        return $this;
    }

    /**
     * Add attribute to sort order
     *
     * @param string $attribute
     * @param string $dir
     * @return Collection
     */
    public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
        $this->getSelect()->order($attribute . " " . $dir);
        return $this;
    }
}

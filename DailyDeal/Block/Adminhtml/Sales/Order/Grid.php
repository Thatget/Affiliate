<?php

namespace MW\DailyDeal\Block\Adminhtml\Sales\Order;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $dailydeal;
    protected $orderCollectionFactory;
    protected $orderConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \MW\DailyDeal\Model\Dailydeal $dailydeal,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\Order\ConfigFactory $orderConfig,
        array $data = []
    ) {
        $this->dailydeal = $dailydeal;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderConfig = $orderConfig;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _prepareCollection()
    {
        $deal_id = $this->getRequest()->getParam('dailydeal_id');
        $model_deal = $this->dailydeal->load($deal_id);
        $orderList = explode(',', $model_deal->getOrderId());
        $collection = $this->orderCollectionFactory->create()
            ->addAttributeToFilter('entity_id', array('in' => $orderList));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('real_order_id', array(
            'header'=> __('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
        ));

        if ($this->_storeManager->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => __('Purchased From (Store)'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
            ));
        }

        $this->addColumn('created_at', array(
            'header' => __('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('billing_name', array(
            'header' => __('Bill to Name'),
            'index' => 'billing_name',
        ));

        $this->addColumn('shipping_name', array(
            'header' => __('Ship to Name'),
            'index' => 'shipping_name',
        ));

        $this->addColumn('base_grand_total', array(
            'header' => __('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => __('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('status', array(
            'header' => __('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => $this->orderConfig->create()->getStatuses(),
        ));

        $this->addColumn(
            'action',
            [
                'header'    => __('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => [
                    [
                        'caption' => __('View'),
                        'url'     => ['base'=>'sales/order/view/'],
                        'field'   => 'order_id'
                    ]
                ],
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            ]
        );

//        $this->addExportType('admin/mui/export/gridToCsv', __('CSV'));
//        $this->addExportType('admin/mui/export/gridToXml', __('Excel XML'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/sales/order/view', array('order_id' => $row->getId()));
    }
}

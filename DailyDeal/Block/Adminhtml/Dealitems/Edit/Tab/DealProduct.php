<?php

namespace MW\DailyDeal\Block\Adminhtml\Dealitems\Edit\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Store\Model\Store;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class DealProduct extends \Magento\Backend\Block\Widget\Grid\Extended implements TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    protected $dailydeal;
    protected $moduleManager;
    protected $dailydealFactory;
    protected $dailydealProductsFactory;
    protected $objectManager;
    protected $status;
    protected $visibility;
    protected $type;
    protected $setsFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \MW\DailyDeal\Model\DailydealFactory $dailydealFactory
     * @param \MW\DailyDeal\Model\Dailydeal $dailydeal
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $status
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Model\Product\Visibility $visibility
     * @param \Magento\Catalog\Model\Product\Type $type
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Registry $coreRegistry,
        \MW\DailyDeal\Model\DailydealFactory $dailydealFactory,
        \MW\DailyDeal\Model\Dailydeal $dailydeal,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->coreRegistry = $coreRegistry;
        $this->dailydealFactory = $dailydealFactory;
        $this->dailydeal = $dailydeal;
        $this->status = $status;
        $this->visibility = $visibility;
        $this->type = $type;
        $this->setsFactory = $setsFactory;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('deal_products');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        if ($this->getProduct()) {
            $this->setDefaultFilter(['in_products' => 1]);
        }
    }

    /**
     * @param Column $column
     * @return $this|Extended
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
    // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            } elseif ($productIds) {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = $this->productFactory
            ->create()
            ->getCollection()
            ->addAttributeToSelect('*');
        $collection->addFieldToFilter('type_id', ['nin'=>['grouped', 'bundle']]);
        $collection->addFieldToFilter('visibility', ['nin'=>[1]]);
        $collection->addFieldToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $dealId = $this->getRequest()->getParam('dailydeal_id');
        $productIds = $this->dailydeal->getProductDealIds($dealId);
        if (count($productIds)) {
            $collection->addFieldToFilter('entity_id', array('nin' => $productIds));
        }
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('in_products', array(
            'header_css_class' => 'a-center',
            'type' => 'radio',
            'html_name' => 'aproducts[]',
            'align' => 'center',
            'index' => 'entity_id',
            'values' => $this->getSelectedProducts(),
        ));

        $this->addColumn('entity_id', array(
            'header' => __('ID'),
            'sortable' => true,
            'width' => '60px',
            'index' => 'entity_id'
        ));

        $this->addColumn('name', array(
            'header' => __('Name'),
            'index' => 'name'
        ));

        $this->addColumn('sku', array(
            'header' => __('SKU'),
            'index' => 'sku'
        ));

        $sets = $this->setsFactory->create()->setEntityTypeFilter(
            $this->productFactory->create()->getResource()->getTypeId()
        )->load()->toOptionHash();
        $this->addColumn(
            'set_name',
            [
                'header'           => __('Attribute Set'),
                'index'            => 'attribute_set_id',
                'type'             => 'options',
                'options'          => $sets,
                'header_css_class' => 'col-attr-name',
                'column_css_class' => 'col-attr-name',
            ]
        );

        $this->addColumn('status', array(
            'header' => __('Status'),
            'width' => '90px',
            'index' => 'status',
            'type' => 'options',
            'html_name' => 'product_status',
            'options' => $this->status->getOptionArray(),
        ));

        $this->addColumn('visibility', array(
            'header' => __('Visibility'),
            'width' => '90px',
            'index' => 'visibility',
            'type' => 'options',
            'options' => $this->visibility->getOptionArray(),
        ));

        $options = $this->type->getOptionArray();
        foreach ($options as $index => $data) {
            if ($index == 'grouped' || $index == 'bundle') {
                unset($options[$index]);
            }
        }
        $options = [''=>' ']+$options;

        $this->addColumn('type_id', array(
            'header' => __('Type'),
            'width' => '80px',
            'index' => 'type_id',
            'type' => 'options',
            'options' => $options,
        ));
        $this->addColumn('price', array(
            'header' => __('Price'),
            'type' => 'currency',
            'currency_code' => (string)$this->_scopeConfig->getValue(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            'index' => 'price'
        ));

        return parent::_prepareColumns();
    }

    protected function getSelectedProducts()
    {
        $products = $this->getProduct();
//        if (!is_array($products)) {
//            $products = array_keys($this->getSelectedRelatedProducts());
//        }
//        $products = [$products];
        return [$products];
    }

    public function getProduct()
    {
        return $this->coreRegistry->registry('deal_product_id');
    }

    public function getSelectedRelatedProducts()
    {
        $products = [];
        $productIds = array($this->getDeal()->getProductId());
        foreach ($productIds as $productId) {
            $products[$productId] = array('position' => 0);
        }
        return $products;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/catalog_product/edit', array('id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl(
            '*/*/productsGrid',
            array('_current' => true)
        );
    }

    public function getDeal()
    {
        return $this->dailydeal->load($this->getRequest()->getParam('dailydeal_id'));
    }

    public function getTabLabel()
    {
        return __('Products');
    }

    public function getTabTitle()
    {
        return __('Products');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}

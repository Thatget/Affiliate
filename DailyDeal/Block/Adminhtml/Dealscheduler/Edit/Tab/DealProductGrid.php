<?php
namespace MW\DailyDeal\Block\Adminhtml\Dealscheduler\Edit\Tab;

use Magento\Store\Model\Store;
use Magento\Backend\Block\Widget\Grid\Extended;

class DealProductGrid extends Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $setsFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $type;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $status;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $visibility;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $websiteFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter
     */
    protected $converter;
    protected $dealSchedulerProduct;
    protected $storeManager;
    protected $dailyDeal;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Product\Type $type
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $status
     * @param \Magento\Catalog\Model\Product\Visibility $visibility
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter $converter
     * @param \MW\DailyDeal\Model\Dealschedulerproduct $dealSchedulerProduct
     * @param \MW\DailyDeal\Model\Dailydeal $dailyDeal
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter $converter,
        \MW\DailyDeal\Model\Dealschedulerproduct $dealSchedulerProduct,
        \MW\DailyDeal\Model\Dailydeal $dailyDeal,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->websiteFactory = $websiteFactory;
        $this->setsFactory = $setsFactory;
        $this->productFactory = $productFactory;
        $this->type = $type;
        $this->status = $status;
        $this->visibility = $visibility;
        $this->moduleManager = $moduleManager;
        $this->coreRegistry = $registry;
        $this->converter = $converter;
        $this->dealSchedulerProduct = $dealSchedulerProduct;
        $this->dailyDeal = $dailyDeal;
        $this->storeManager = $storeManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('productGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false); // reset filter when load edit page
        $this->setUseAjax(true);
        if ($this->getProduct()) {
            $this->setDefaultFilter(['in_products' => 1]);
        }
    }

    public function getDealScheduler()
    {
        return $this->coreRegistry->registry('current_deal_scheduler');
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->storeManager->getStore($storeId);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareCollection()
    {
        $store = $this->getStore();
        $collection = $this->productFactory->create()->getCollection()->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'attribute_set_id'
        )->addAttributeToSelect(
            'type_id'
        )->addAttributeToFilter(
            'type_id',
            ['in' => ['simple', 'virtual', 'configurable', 'downloadable']]
        )->setStore(
            $store
        );

        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $collection->joinField(
                'qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );
        }
        if ($store->getId()) {
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                Store::DEFAULT_STORE_ID
            );
            $collection->joinAttribute(
                'custom_name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $store->getId());
        } else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }
        $collection->addFieldToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

        if ($this->getRequest()->getParam('deal_scheduler_id') != null) {
            $dealSchedulerId = $this->getRequest()->getParam('deal_scheduler_id');
            $productDeal = $this->dealSchedulerProduct->getCollection();
            $productDeal->addFieldToFilter('deal_scheduler_id', ['nin' => $dealSchedulerId]);
            $productIds = [];
            foreach ($productDeal as $deal) {
                $productIds[] = $deal->getProductId();
            }
            if (count($productIds) > 0) {
                $collection->addFieldToFilter('entity_id', ['nin' => $productIds]);
            }
        } else {
            $productDeal = $this->dealSchedulerProduct->getCollection();
            $productIds = [];
            foreach ($productDeal as $deal) {
                $productIds[] = $deal->getProductId();
            }
            if (count($productIds) > 0) {
                $collection->addFieldToFilter('entity_id', ['nin' => $productIds]);
            }
        }
//        $dealItems = $this->dailyDeal->getCollection();
//        $productDealSelected = [];
//        foreach ($dealItems as $dealItem) {
//            $productDealSelected[] = $dealItem->getProductId();
//        }
//        if (count($productDealSelected) > 0) {
//            $collection->addFieldToFilter('entity_id', ['nin' => $productDealSelected]);
//        }

        $collection->addFieldToFilter('visibility', ['nin'=>[1]]);
        $this->setCollection($collection);

        $this->getCollection()->addWebsiteNamesToResult();

        parent::_prepareCollection();

        return $this;
    }

    /**
     * get selected row values.
     *
     * @return array
     */
    public function getSelectedDealSchedulerProducts()
    {
        $products = array();
        $dealProductItems = $this->coreRegistry->registry('products');
        if (is_array($dealProductItems)) {
            foreach ($dealProductItems as $dealItem) {
                $products[$dealItem['product_id']] = array('product_deal_qty' => $dealItem['product_deal_qty']);
            }
        }
        return $products;
    }

    protected function getSelectedProductsGrid()
    {
        if ($this->getProduct()) {
            $products = $this->getProduct();
            return $products;
        } else {
            return [];
        }
    }

    protected function getProduct()
    {
//        return $this->coreRegistry->registry('deal_product_id');
        if ($this->getRequest()->getParam('deal_scheduler_id') != null) {
            $products =  $this->dealSchedulerProduct
                ->getProductOptionArray($this->getRequest()->getParam('deal_scheduler_id'));
            return array_keys($products);
        } else {
            return [];
        }
//        return $this->coreRegistry->registry('products');
    }

    /**
     * get selected stores in serilaze grid store.
     *
     * @return array
     */
    public function getTreeSelectedStores()
    {
        $deal_product_ids = $this->getRequest()->getParam('deal_product_ids');
        if (is_array($deal_product_ids) && !empty($deal_product_ids)) {
            $ids = $deal_product_ids;
        } else {
            $ids = $this->getSelectedProducts();
        }
        return $this->converter->toTreeArray($ids);
    }

    protected function getSelectedProducts()
    {
        $products = $this->getProduct();
        if (!is_array($products)) {
            $products = array_keys($this->getSelectedDealSchedulerProducts());
        }
        return $products;
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
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

    /**
     * @throws
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_products',
            [
                'type' => 'checkbox',
                'name' => 'entity_id',
                'values' => $this->getSelectedProductsGrid(),
                'index' => 'entity_id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction',
                'data-form-part' => 'dailydeal_dealscheduler_form',
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'class' => 'xxx'
            ]
        );

        $store = $this->getStore();
        if ($store->getId()) {
            $this->addColumn(
                'custom_name',
                [
                    'header' => __('Name in %1', $store->getName()),
                    'index' => 'custom_name',
                    'header_css_class' => 'col-name',
                    'column_css_class' => 'col-name'
                ]
            );
        }
        $options = $this->type->getOptionArray();
        foreach ($options as $index => $data) {
            if ($index == 'grouped' || $index == 'bundle') {
                unset($options[$index]);
            }
        }
        $options = [''=>' ']+$options;
        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'index' => 'type_id',
                'type' => 'options',
                'options' => $options
            ]
        );

        $sets = $this->setsFactory->create()->setEntityTypeFilter(
            $this->productFactory->create()->getResource()->getTypeId()
        )->load()->toOptionHash();
        $sets = [''=>' ']+$sets;
        $this->addColumn(
            'set_name',
            [
                'header' => __('Attribute Set'),
                'index' => 'attribute_set_id',
                'type' => 'options',
                'options' => $sets,
                'header_css_class' => 'col-attr-name',
                'column_css_class' => 'col-attr-name'
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku'
            ]
        );

        $store = $this->getStore();
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
            ]
        );

        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $this->addColumn(
                'qty',
                [
                    'header' => __('Quantity'),
                    'type' => 'number',
                    'index' => 'qty'
                ]
            );
        }

        $visibility = [''=>' ']+$this->visibility->getOptionArray();
        $this->addColumn(
            'visibility',
            [
                'header' => __('Visibility'),
                'index' => 'visibility',
                'type' => 'options',
                'options' => $visibility,
                'header_css_class' => 'col-visibility',
                'column_css_class' => 'col-visibility'
            ]
        );

//        $this->addColumn(
//            'status',
//            [
//                'header' => __('Status'),
//                'index' => 'product_status',
//                'type' => 'options',
//                'name'  => 'product_status',
//                'options' => $this->status->getOptionArray()
//            ]
//        );

        $this->addColumn(
            'product_deal_qty',
            [
                'header' => __('Deal Qty'),
                'name' => 'product_deal_qty',
                'type' => 'number',
                'validate_class' => 'validate-number',
                'index' => 'product_deal_qty',
                'editable' => true,
                'html_name' => 'product_deal_qty',
                'header_css_class' => 'col-position',
                'column_css_class' => 'col-position',
                'filter_condition_callback' => [$this, 'filterProductPosition']
            ]
        );

//        $this->addColumn(
//            'deal_position',
//            [
//                'header' => __('Position'),
//                'name' => 'deal_position',
//                'type' => 'number',
//                'validate_class' => 'validate-number',
//                'index' => 'deal_position',
//                'editable' => true,
//                'header_css_class' => 'col-position',
//                'column_css_class' => 'col-position',
//                'filter_condition_callback' => [$this, 'filterProductPosition']
//            ]
//        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * Apply `position` filter to cross-sell grid.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection $collection
     * @param \Magento\Backend\Block\Widget\Grid\Column\Extended $column
     * @return $this
     */
    public function filterProductPosition($collection, $column)
    {
        $collection->addLinkAttributeToFilter($column->getIndex(), $column->getFilter()->getCondition());
        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('dailydeal/*/dealproducttab', ['_current' => true]);
    }

    protected function getProducts()
    {
        if ($products = $this->getRequest()->getPost('deal_product_ids', null)) {
            return $products;
        } else {
            if ($productss = $this->getRequest()->getParam('deal_product_ids', null)) {
                return explode(',', $productss);
            } else {
                return [];
            }
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return "";
    }

    /**
     * @return array
     */
    public function dataCallback()
    {
        $this->setData('comment_deal', 1);
        return $this->getProduct();
    }
}

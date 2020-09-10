<?php

namespace MW\DailyDeal\Block\Sidebar;

use Magento\Store\Model\ScopeInterface;

class TodayDeal extends \Magento\Framework\View\Element\Template
{
    protected $checkoutSession;
    protected $sessionManager;
    protected $dailydealModel;
    protected $dailydealCollectionFactory;
    /**
     * @var \MW\DailyDeal\Model\ResourceModel\Dailydeal\CollectionFactory
     */
    protected $salesruleCollectionFactory;

    protected $registry;

    protected $helperConfig;

    protected $productRepository;

    protected $pricingHelper;

    protected $date;

    protected $storeManager;

    protected $helperDailyDeal;

    protected $resource;

    protected $business;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \MW\DailyDeal\Model\Dailydeal $dailydealModel,
        \MW\DailyDeal\Model\ResourceModel\Dailydeal\CollectionFactory $dailydealCollectionFactory,
        \Magento\Framework\Registry $registry,
        \MW\DailyDeal\Helper\Config $helperConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MW\DailyDeal\Helper\Data $helperDailyDeal,
        \Magento\Framework\App\ResourceConnection $resource,
        \MW\DailyDeal\Model\Business $business,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->dailydealModel = $dailydealModel;
        $this->dailydealCollectionFactory = $dailydealCollectionFactory;
        $this->registry = $registry;
        $this->helperConfig = $helperConfig;
        $this->productRepository = $productRepository;
        $this->pricingHelper = $pricingHelper;
        $this->date = $date;
        $this->storeManager = $storeManager;
        $this->helperDailyDeal = $helperDailyDeal;
        $this->resource = $resource;
        $this->business = $business;
        parent::__construct($context, $data);
    }

    public function getActiveDeals()
    {
        return $this->dailydealModel->getActiveDeals();
    }

    public function getNumberrActiveDeal()
    {
        return $this->helperConfig->getConfigNumberDealActive();
    }

    public function getConfigSchemeColor()
    {
        return $this->helperConfig->getConfigSchemeColor();
    }

    public function getConfigCountdownColor()
    {
        return $this->helperConfig->getConfigCountDownColor();
    }

    public function getConfigHighlightColor()
    {
        return $this->helperConfig->getConfigHightLightColor();
    }

    public function getProductById($productId)
    {
        return $this->productRepository->getById($productId);
    }

    public function addPriceWithCurrency($price)
    {
        return $this->pricingHelper->currency($price, true, false);
    }

    public function getConfigDisplayQuantity()
    {
        return $this->helperConfig->getConfigDisplayQuantity();
    }

    public function getConfigTodayDealBlockTitle()
    {
        return $this->helperConfig->getConfigTodayDealBlockTitle();
    }

    public function getDeals()
    {
        $store_id = $this->storeManager->getStore()->getId();
        $connection = $this->resource
            ->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $tblCatalogStockItem = $connection->getTableName('cataloginventory_stock_item');
        $currenttime = date('Y-m-d H:i:s', $this->date->scopeTimeStamp());

        $collection = $this->dailydealModel->getCollection()
            ->addFieldToFilter('status', \MW\DailyDeal\Model\Status::STATUS_TIME_RUNNING)
//            ->addFieldToFilter('expire', \MW\DailyDeal\Model\Status::STATUS_EXPIRE_FALSE)
//            ->addFieldToFilter('store_view', array(array('like' => '%' . $store_id . '%'), array('like' => '0')))
//            ->addFieldToFilter('start_date_time', array('to' => $currenttime))
//            ->addFieldToFilter('end_date_time', array('from' => $currenttime))
            ->addProductStatusFilter($store_id)
            ->getConfigSortBy();

//        $collection->getSelect()->joinLeft(
//            array(
//                'stock' => $tblCatalogStockItem),
//            'stock.product_id = main_table.product_id',
//            array('stock.qty', 'stock.is_in_stock')
//        );

//        $collection->getSelect()
//            ->where("stock.is_in_stock = " . \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK);

        return $collection;
    }

    public function increateFeaturedView($dealId)
    {
        return $this->business->increateFeaturedView($dealId);
    }

    public function getTimeStamp()
    {
        return $this->date->scopeTimeStamp();
    }

    public function getProductImageUrl($product)
    {
        $store = $this->storeManager->getStore();

        $productImageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            . 'catalog/product' . $product->getImage();
        $productUrl  = $product->getProductUrl();
        return $productImageUrl;
    }

    public function getConfigTodayDealShowDetail()
    {
        return $this->helperConfig->getConfigTodayDealShowDetail();
    }

    public function renderDealQtyOnCatalogPage($qty)
    {
        return $this->helperDailyDeal->renderDealQtyOnCatalogPage($qty);
    }

    public function getAddToCartUrl($product)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $listBlock = $objectManager->get('\Magento\Catalog\Block\Product\ListProduct');

        $addToCartUrl =  $listBlock->getAddToCartUrl($product);
        return $addToCartUrl;
    }

    public function isShowTodayDeals()
    {
        if (!$this->helperConfig->isEnabled()) {
            return false;
        }
        return $this->helperConfig->getConfigShowTodayDealBlock();
    }
}

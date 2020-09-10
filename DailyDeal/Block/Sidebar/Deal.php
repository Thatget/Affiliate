<?php

namespace MW\DailyDeal\Block\Sidebar;

use Magento\Store\Model\ScopeInterface;

class Deal extends \Magento\Framework\View\Element\Template
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

    protected $storeManager;

    public $date;

    protected $resource;

    protected $asset;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \MW\DailyDeal\Model\Dailydeal $dailydealModel,
        \MW\DailyDeal\Model\ResourceModel\Dailydeal\CollectionFactory $dailydealCollectionFactory,
        \Magento\Framework\Registry $registry,
        \MW\DailyDeal\Helper\Config $helperConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\View\Asset\Repository $asset,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->dailydealModel = $dailydealModel;
        $this->dailydealCollectionFactory = $dailydealCollectionFactory;
        $this->registry = $registry;
        $this->helperConfig = $helperConfig;
        $this->productRepository = $productRepository;
        $this->pricingHelper = $pricingHelper;
        $this->storeManager = $storeManager;
        $this->date = $date;
        $this->resource = $resource;
        $this->asset = $asset;
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

    public function getTimeStamp()
    {
        return $this->date->scopeTimeStamp();
    }

    public function getProductImageUrl($product)
    {
        $store = $this->storeManager->getStore();

        $productImageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            . 'catalog/product'
            . $product->getImage();
        $productUrl  = $product->getProductUrl();
        return $productImageUrl;
    }

    public function getDealData()
    {
        $deals = $this->dailydealModel->getActiveDeals();
        $store_id = $this->storeManager->getStore()->getId();
        /* @var $deals \MW\DailyDeal\Model\ResourceModel\Dailydeal\Collection */
        $deals->addFieldToFilter('status', \MW\DailyDeal\Model\Status::STATUS_ENABLED)
            ->addFieldToFilter('expire', \MW\DailyDeal\Model\Status::STATUS_EXPIRE_FALSE)
            ->addFieldToFilter('store_view', array(array('like'=>'%'. $store_id .'%'), array('like'=>'0')))
            ->addProductStatusFilter($store_id)
            ->getConfigSortBy();

        return $deals;
    }
}

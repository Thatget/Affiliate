<?php

namespace MW\DailyDeal\Block\Sidebar;

use Magento\Store\Model\ScopeInterface;

class FeatureDeal extends \Magento\Framework\View\Element\Template
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

    protected $asset;

    protected $date;

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
        \Magento\Framework\View\Asset\Repository $asset,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
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
        $this->asset = $asset;
        $this->date = $date;
        $this->setTemplate('MW_DailyDeal::sidebar/featureDeal.phtml');
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

    public function getProductImageUrl($product)
    {
        $store = $this->storeManager->getStore();

        $productImageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            . 'catalog/product' . $product->getImage();
        $productUrl  = $product->getProductUrl();
        return $productImageUrl;
    }

    public function getAddToCartUrl($product)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $listBlock = $objectManager->get('\Magento\Catalog\Block\Product\ListProduct');

        $addToCartUrl =  $listBlock->getAddToCartUrl($product);
        return $addToCartUrl;
    }

    public function isShowActiveDeals()
    {
        return $this->helperConfig->getConfigShowActiveDealBlock();
    }

    /**
     * @param string $asset
     * @return string
     */
    //This function will be used to get the css/js file.
    public function getAssetUrl($asset)
    {
        $assetRepository = $this->asset;
        return $assetRepository->createAsset($asset)->getUrl();
    }

    public function getFeatureDeals()
    {
        if (!$this->helperConfig->isEnabled()) {
            return [];
        }
        $featured = $this->dailydealModel->getFeaturedDeals();
        return $featured;
    }

    public function getTimeStamp()
    {
        return $this->date->scopeTimeStamp();
    }

    public function getConfigTodayDealShowDetail()
    {
        return $this->helperConfig->getConfigTodayDealShowDetail();
    }
}

<?php
namespace MW\DailyDeal\Observer\Frontend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Model\Session as CustomerModelSession;

class CatalogFinalPrice implements ObserverInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;
    /**
     * @var CustomerModelSession
     */
    protected $customerSession;
    protected $helperConfig;
    protected $dailydealModel;
    protected $productloader;

    /**
     * Initialize dependencies.
     *
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param \MW\DailyDeal\Helper\Config $helperConfig
     * @param \MW\DailyDeal\Model\Dailydeal $dailydealModel
     * @param \Magento\Catalog\Model\ProductFactory $productloader
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        \MW\DailyDeal\Helper\Config $helperConfig,
        \MW\DailyDeal\Model\Dailydeal $dailydealModel,
        \Magento\Catalog\Model\ProductFactory $productloader,
        CustomerModelSession $customerSession
    ) {
        $this->storeManager = $storeManager;
        $this->localeDate = $localeDate;
        $this->helperConfig = $helperConfig;
        $this->dailydealModel = $dailydealModel;
        $this->productloader = $productloader;
        $this->customerSession = $customerSession;
    }

    /**
     * Add option discount.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return CatalogFinalPrice
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helperConfig->isEnabled()) {
            return $this;
        }

        $product = $observer->getEvent()->getProduct();
        $deal = $this->dailydealModel;
        if ($product->getParentProductId()) {
            $idProductForDeal = $product->getParentProductId();
        } else {
            $idProductForDeal = $product->getId();
        }
        $deal->loadActiveDealByProductId($idProductForDeal);
        if ($deal->getId()) {
            $this->setProductPriceAndWatermark($product);
        }
        return $this;
    }

    protected function setProductPriceAndWatermark($model_product)
    {
        $dailydeal_collection = $this->dailydealModel->getCollection();
        $model_deal = $dailydeal_collection->loadcurrentdeal($model_product->getId());
        if ($model_deal) {
            $flag_qty = $model_deal->checkDealQty($model_product, $model_deal);
            $flag_price = $model_deal->checkDealPrice($model_product);
            if ($flag_qty && $flag_price) {
                $discount = $model_deal->getDiscount();
                $priceDeal = $model_product->getPrice() * (100 - $discount) / 100;
                if ($priceDeal != 0) {
                    $model_product->setSpecialPrice($priceDeal);
                    $model_product->setFinalPrice($priceDeal);
                }
            }
        }
    }
}

<?php
namespace MW\DailyDeal\Observer\Frontend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Model\Session as CustomerModelSession;
use MW\DailyDeal\Model\Status;

class CatalogProductCollectionLoadAfter implements ObserverInterface
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
    protected $productRepository;
    protected $configurable;

    /**
     * Initialize dependencies.
     *
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param \MW\DailyDeal\Helper\Config $helperConfig
     * @param \MW\DailyDeal\Model\Dailydeal $dailydealModel
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        \MW\DailyDeal\Helper\Config $helperConfig,
        \MW\DailyDeal\Model\Dailydeal $dailydealModel,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable,
        CustomerModelSession $customerSession
    ) {
        $this->storeManager = $storeManager;
        $this->localeDate = $localeDate;
        $this->helperConfig = $helperConfig;
        $this->dailydealModel = $dailydealModel;
        $this->productRepository = $productRepository;
        $this->configurable = $configurable;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helperConfig->isEnabled()) {
            return;
        }

        $deal = $this->dailydealModel;
        foreach ($observer->getCollection()->getItems() as $product) {
            $productConfig = $this->configurable
                ->getParentIdsByChild($product->getId());
            $parentProduct = null;
            if (isset($productConfig[0])) {
                //this is parent product id..
                $productParentId = $productConfig[0];
                $parentProduct = $this->productRepository->getById($productParentId);

                $deal->setData([]);
                $deal->loadByProductId($parentProduct->getId());

                if ($deal->getId()) {
                    $this->setProductPriceAndWatermark($product, $parentProduct);
                }
            } else {
                $deal->setData([]);
                $deal->loadByProductId($product->getId());
                if ($deal->getId()) {
                    $this->setProductPriceAndWatermarkSimple($product);
                }
            }
        }
    }

    protected function setProductPriceAndWatermark($model_product, $parentProduct)
    {
        $dailydeal_collection = $this->dailydealModel->getCollection();
        /** @var \MW\DailyDeal\Model\Dailydeal $model_deal */
        $model_deal = $dailydeal_collection->loadcurrentdeal($parentProduct->getId());
        if ($model_deal) {
            $flag_qty = $model_deal->checkDealQty($model_product, $model_deal);
            $flag_price = $model_deal->checkDealPrice($model_product);
            if ($flag_qty && $flag_price) {
                $model_product->setSpecialPrice($model_deal->getDailydealPrice());
                $model_product->setFinalPrice($model_deal->getDailydealPrice());
            } elseif ($model_product->getTypeId() == Status::TYPE_CONFIGURABLE) {
                $model_deal->setMinPriceFollowProductType($model_product, $model_deal);
                $model_product->setSpecialPrice($model_product->getData('min_price'));
                $model_product->setFinalPrice($model_product->getData('min_price'));
                $model_product->setRegularPrice($model_product->getData('min_price'));
                $model_product->setMinimalPrice($model_product->getData('min_price'));
            }
        }
    }

    protected function setProductPriceAndWatermarkSimple($model_product)
    {
        $dailydeal_collection = $this->dailydealModel->getCollection();
        /** @var \MW\DailyDeal\Model\Dailydeal $model_deal */
        $model_deal = $dailydeal_collection->loadcurrentdeal($model_product->getId());
        if ($model_deal) {
            $flag_qty = $model_deal->checkDealQty($model_product, $model_deal);
            $flag_price = $model_deal->checkDealPrice($model_product);
            if ($flag_qty && $flag_price) {
                $model_product->setSpecialPrice($model_deal->getDailydealPrice());
                $model_product->setFinalPrice($model_deal->getDailydealPrice());
            } elseif ($model_product->getTypeId() == Status::TYPE_CONFIGURABLE) {
                $model_deal->setMinPriceFollowProductType($model_product, $model_deal);
                $model_product->setSpecialPrice($model_product->getData('min_price'));
                $model_product->setFinalPrice($model_product->getData('min_price'));
                $model_product->setRegularPrice($model_product->getData('min_price'));
                $model_product->setMinimalPrice($model_product->getData('min_price'));
            }
        }
    }
}

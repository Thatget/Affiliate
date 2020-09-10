<?php

namespace MW\DailyDeal\Observer\Frontend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Model\Session as CustomerModelSession;
use Magento\Quote\Model\Quote;
use Magento\Checkout\Model\Cart as CustomerCart;

class ProcessApply implements ObserverInterface
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
    protected $productDealModel;
    protected $orderDealModel;
    protected $helperDeal;
    protected $configurable;

    /**
     * Initialize dependencies.
     *
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param \MW\DailyDeal\Helper\Config $helperConfig
     * @param \MW\DailyDeal\Model\Dailydeal $dailydealModel
     * @param \MW\DailyDeal\Model\ProductDeal $productDealModel
     * @param \MW\DailyDeal\Model\Order $orderDealModel
     * @param \MW\DailyDeal\Helper\Data $helperDeal
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        \MW\DailyDeal\Helper\Config $helperConfig,
        \MW\DailyDeal\Model\Dailydeal $dailydealModel,
        \MW\DailyDeal\Model\ProductDeal $productDealModel,
        \MW\DailyDeal\Model\Order $orderDealModel,
        \MW\DailyDeal\Helper\Data $helperDeal,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable,
        CustomerModelSession $customerSession
    ) {
        $this->storeManager = $storeManager;
        $this->localeDate = $localeDate;
        $this->helperConfig = $helperConfig;
        $this->dailydealModel = $dailydealModel;
        $this->productDealModel = $productDealModel;
        $this->orderDealModel = $orderDealModel;
        $this->helperDeal = $helperDeal;
        $this->configurable = $configurable;
        $this->customerSession = $customerSession;
    }

    /**
     * Apply catalog rules to product on frontend
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helperConfig->isEnabled()) {
            return $this;
        }

        $this->_processRulePrice($observer);
        return $this;
    }

    /**
     * Process price for free product.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function _processRulePrice(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $item \Magento\Quote\Model\Quote\Item */
        $item = $observer->getEvent()->getQuoteItem();
        if ($item->getParentItem()) {
            $item = $item->getParentItem();
        }

        $product = $this->configurable
            ->getParentIdsByChild($item->getProductId());
        $parentId = 0;
        if (isset($product[0])) {
            //this is parent product id..
            $parentId = $product[0];
        }

        /** @var $deal \MW\DailyDeal\Model\Dailydeal */
        $deal = $this->dailydealModel->loadByProductId($item->getProductId());
        if ($deal) {
            if ($item->getProductType() == 'configurable') {
                $childItems = $item->getChildren();
                foreach ($childItems as $childItem) {
                    $childProduct = $childItem->getProduct();
                    $childPrice = $childProduct->getPrice();
                    if ($deal->getId()) {
                        $discount = $deal->getDiscount();
                        $newPrice = $childPrice * (100 - $discount) / 100;
                        $item->setCustomPrice($newPrice);
                        $item->setOriginalCustomPrice($newPrice);
                        $item->getProduct()->setIsSuperMode(true);
                        $info = $this->helperDeal->decodeData($item->getOptionByCode('info_buyRequest')->getValue());
                        $info['dailydeal_id'] = $deal->getId();
                        $item->getOptionByCode('info_buyRequest')->setValue($this->helperDeal->encodeData($info));
                    }
                }
            } elseif ($item->getProductType() == 'bundle') {
                $discount = $deal->getDiscount();
                $childItems = $item->getQtyOptions();
                $itemsPrice = 0;
                foreach ($childItems as $childItem) {
                    $productPrice = $childItem->getProduct()->getPrice();
                    $qtyItem = $childItem->getValue();
                    $priceItem = $productPrice * $qtyItem;
                    $itemsPrice += $priceItem;
                }
                $discountPrice = $itemsPrice * (100 - $discount) / 100;
                $item->setCustomPrice($discountPrice);
                $item->setOriginalCustomPrice($discountPrice);
                $item->setPrice($discountPrice);
                $item->setBasePrice($discountPrice);
                $item->getProduct()->setIsSuperMode(true);
                $info = $this->helperDeal->decodeData($item->getOptionByCode('info_buyRequest')->getValue());
                $info['dailydeal_id'] = $deal->getId();
                $item->getOptionByCode('info_buyRequest')->setValue($this->helperDeal->encodeData($info));
            } elseif ($deal) {
                $item->setCustomPrice($deal->getDailydealPrice());
                $item->setPrice($deal->getDailydealPrice());
                $item->setBasePrice($deal->getDailydealPrice());
                $item->setOriginalCustomPrice($deal->getDailydealPrice());
                $item->getProduct()->setIsSuperMode(true);
                $info = $this->helperDeal->decodeData($item->getOptionByCode('info_buyRequest')->getValue());
                $info['dailydeal_id'] = $deal->getId();
                $item->getOptionByCode('info_buyRequest')->setValue($this->helperDeal->encodeData($info));
            }
        } elseif ($parentId) {
            $dealParent = $this->dailydealModel->loadByProductId($parentId);
            if ($dealParent) {
                $discount = $dealParent->getDiscount();
                $newPrice = $item->getProduct()->getPrice() * (100 - $discount) / 100;
                $item->setCustomPrice($newPrice);
                $item->setOriginalCustomPrice($newPrice);
                $item->getProduct()->setIsSuperMode(true);
                $info = $this->helperDeal->decodeData($item->getOptionByCode('info_buyRequest')->getValue());
                $info['dailydeal_id'] = $dealParent->getId();
                $item->getOptionByCode('info_buyRequest')->setValue($this->helperDeal->encodeData($info));
            }
        }
    }
}

<?php
namespace MW\DailyDeal\Observer\Frontend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Model\Session as CustomerModelSession;

class CheckoutCartUpdateItemsAfter implements ObserverInterface
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
    protected $productFatory;

    /**
     * Initialize dependencies.
     *
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param \MW\DailyDeal\Helper\Config $helperConfig
     * @param \MW\DailyDeal\Model\Dailydeal $dailydealModel
     * @param \MW\DailyDeal\Model\ProductDeal $productDealModel
     * @param \MW\DailyDeal\Model\Order $orderDealModel
     * @param \Magento\Catalog\Model\ProductFactory $productFatory
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        \MW\DailyDeal\Helper\Config $helperConfig,
        \MW\DailyDeal\Model\Dailydeal $dailydealModel,
        \MW\DailyDeal\Model\ProductDeal $productDealModel,
        \MW\DailyDeal\Model\Order $orderDealModel,
        \Magento\Catalog\Model\ProductFactory $productFatory,
        CustomerModelSession $customerSession
    ) {
        $this->storeManager = $storeManager;
        $this->localeDate = $localeDate;
        $this->helperConfig = $helperConfig;
        $this->dailydealModel = $dailydealModel;
        $this->productDealModel = $productDealModel;
        $this->orderDealModel = $orderDealModel;
        $this->productFatory = $productFatory;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return CheckoutCartUpdateItemsAfter
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helperConfig->isEnabled()) {
            return $this;
        }

        $cart = $observer->getData('cart');
        $info = $observer->getData('info');

        foreach ($info as $dataId => $dataInfo) {
            $item = $cart->getData('quote')->getItemById($dataId);
            /** @var \MW\DailyDeal\Model\Dailydeal $deal */
            $deal = $this->dailydealModel->getCollection()->loadcurrentdeal($item->getProductId());
            if ($deal) {
                /* Check deal's qty */
                $model_product = $this->productFatory->create()->load($item->getData('product_id'));
                $flag_sold_qty = $deal->checkSoldQty($model_product, $deal, $dataInfo['qty']);

                if (!$flag_sold_qty) {
                    $dealqty = $deal->getDealQty();
                    $soldqty = $deal->getSoldQty();
                    $qty_left = $dealqty - $soldqty;

                    $this->customerSession->addError(
                        "The quantity that you have inserted is over deal quantity left ($qty_left). 
                        Please reinsert another one!"
                    );
                }
            }
        }
        return $this;
    }
}

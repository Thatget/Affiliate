<?php
namespace MW\DailyDeal\Observer\Frontend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Model\Session as CustomerModelSession;

class SalesOrderSaveAfter implements ObserverInterface
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
    protected $helperDailydeal;
    protected $order;

    /**
     * Initialize dependencies.
     *
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param \MW\DailyDeal\Helper\Config $helperConfig
     * @param \MW\DailyDeal\Model\Dailydeal $dailydealModel
     * @param \MW\DailyDeal\Model\ProductDeal $productDealModel
     * @param \MW\DailyDeal\Model\Order $orderDealModel
     * @param \MW\DailyDeal\Helper\Data $helperDailydeal
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        \MW\DailyDeal\Helper\Config $helperConfig,
        \MW\DailyDeal\Model\Dailydeal $dailydealModel,
        \MW\DailyDeal\Model\ProductDeal $productDealModel,
        \MW\DailyDeal\Model\Order $orderDealModel,
        \MW\DailyDeal\Helper\Data $helperDailydeal,
        \Magento\Sales\Api\Data\OrderInterface $order,
        CustomerModelSession $customerSession
    ) {
        $this->storeManager = $storeManager;
        $this->localeDate = $localeDate;
        $this->helperConfig = $helperConfig;
        $this->dailydealModel = $dailydealModel;
        $this->productDealModel = $productDealModel;
        $this->orderDealModel = $orderDealModel;
        $this->helperDailydeal = $helperDailydeal;
        $this->order = $order;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return SalesOrderSaveAfter
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helperConfig->isEnabled()) {
            return $this;
        }
        $order = $observer->getEvent()->getOrder();
        $statusConfig = $this->helperConfig->getConfigStatusOrderToSold();
        if ($order->getStatus() == $statusConfig) {
            $items = $order->getAllVisibleItems();
            $orderIncrement = $order->getData('increment_id');
            $currentOrder = $this->order->loadByIncrementId($orderIncrement);
            $order_id = $currentOrder->getId();
            foreach ($items as $item) {
                $productId = $item->getProductId();
                if ($productId) {
                    $deal = $this->dailydealModel->loadByProductId($productId);
                    if ($deal) {
                        $sold_qty = $item->getData('qty_ordered') + $deal->getData('sold_qty');
                        $deal->setSoldQty($sold_qty);
                        $deal->insertOrderId($order_id);
                        $deal->checkDealQtyToEndDeal($sold_qty);
                        $deal->save();
//                         Action : disable product after place order success, update deal success
                        $this->productDealModel->disableProductByDealId(array($deal->getId()));

                        $info_buyRequest = $this->orderDealModel->markProductOfDeal($item, $deal);
                        $item->setProductOptions($info_buyRequest);
                    }
                }
            }
        }
        return $this;
    }
}

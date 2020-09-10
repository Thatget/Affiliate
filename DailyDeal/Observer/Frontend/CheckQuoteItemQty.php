<?php
namespace MW\DailyDeal\Observer\Frontend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Model\Session as CustomerModelSession;

class CheckQuoteItemQty implements ObserverInterface
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
     * @param \Magento\Framework\Event\Observer $observer
     * @return CheckQuoteItemQty
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helperConfig->isEnabled()) {
            return;
        }
        $quoteItem = $observer->getData('item');
        $qty = $quoteItem->getData('qty');

        $result = new \Magento\Framework\DataObject();
        $result->setData('has_error', false);
        /** @var $deal \MW\DailyDeal\Model\Dailydeal */
        $deal = $this->dailydealModel->getCollection()->loadcurrentdeal($quoteItem->getData('product_id'));

        if ($deal != null) {
            // Check deal's qty
            $currentQty = $deal->getData('deal_qty') - $deal->getData('sold_qty');

            $product_id = $quoteItem->getData('product_id');
            $model_product = $this->productloader->create()->load($product_id);

            if (!$deal->checkSoldQty($model_product, $deal, $qty)) {
                $message = __(
                    'The requested quantity for %1 not available in Deal. Deal quantity left: %2',
                    $deal->getData('cur_product'),
                    $currentQty
                );
                $result->setData('has_error', true)
                    ->setData('message', $message)
                    ->setData('quote_message', $message)
                    ->setData('quote_message_index', 'qty');
            }

            // Check Limit deals per customer
            $totalLimit = $deal->getData('limit_customer');
            if ($totalLimit > 0) {
                if ($qty > $totalLimit) {
                    $message = __("Quantity you chose exceed the deal quantity (%1) that you are allowed to buy!", $totalLimit);
                    $result->setData('has_error', true)
                        ->setData('message', $message)
                        ->setData('quote_message', $message)
                        ->setData('quote_message_index', 'qty');
                }
            }

            if ($result->getHasError()) {
                $quoteItem->setHasError(true)
                    ->setMessage($result->getMessage());
                $quoteItem->getQuote()->setHasError(true)
                    ->addMessage($result->getQuoteMessage(), $result->getQuoteMessageIndex());
            }
        }
        return $this;
    }
}

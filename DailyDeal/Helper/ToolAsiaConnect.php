<?php
namespace MW\DailyDeal\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Session as CustomerModelSession;

class ToolAsiaConnect extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $checkoutSession;
    protected $layoutFactory;
    protected $layout;
    protected $cart;
    /**
     * @var CustomerModelSession
     */
    protected $customerSession;
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    protected $helperConfig;
    protected $helperDailyDeal;

    /**
     * ToolAsiaConnect constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param Config $helperConfig
     * @param Data $helperDailyDeal
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param CustomerModelSession $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \MW\DailyDeal\Helper\Config $helperConfig,
        \MW\DailyDeal\Helper\Data $helperDailyDeal,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        CustomerModelSession $customerSession
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->layoutFactory = $layoutFactory;
        $this->layout = $layout;
        $this->cart = $cart;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->helperConfig = $helperConfig;
        $this->helperDailyDeal = $helperDailyDeal;
        $this->productMetadata = $productMetadata;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     *
     * @param string $date_time '2013-04-13 04:51:15'
     * @param int $type
     * 1    : hours
     * 2    : minutes
     * 3    : seconds
     * 4    : month
     * 5    : days
     * 6    : year
     * 7    : week
     * @param string $format 'Y-m-d H:i:s'
     * @return false|int|string
     */
    public static function increaseTime($date_time, $type, $value, $format)
    {
        if ($value > 0) {
            $char = '+ ';
        }

        if ($type == 1) {
            $time = strtotime($char . $value . ' hours', strtotime($date_time));
        } elseif ($type == 3) {
            $time = strtotime($char . $value . ' seconds', strtotime($date_time));
        } elseif ($type == 5) {
            $time = strtotime($char . $value . ' days', strtotime($date_time));
        }

        $data = $time;
        if ($format != null) {
            $data = date($format, $time);
        }
        return $data;
    }
}

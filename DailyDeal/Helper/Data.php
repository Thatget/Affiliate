<?php
namespace MW\DailyDeal\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Session as CustomerModelSession;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
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
    protected $productMetadata;
    protected $inlineTranslation;
    protected $escaper;
    protected $transportBuilder;
    protected $logger;
    protected $order;
    protected $serializer;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \MW\DailyDeal\Helper\Config $helperConfig,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        CustomerModelSession $customerSession
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->layoutFactory = $layoutFactory;
        $this->layout = $layout;
        $this->cart = $cart;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->helperConfig = $helperConfig;
        $this->productMetadata = $productMetadata;
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $context->getLogger();
        $this->order = $order;
        $this->serializer = $serializer;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    public function getStoreConfig($xmlPath)
    {
        return $this->scopeConfig->getValue(
            $xmlPath,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrlImageCatalogList()
    {
        if (!$this->scopeConfig->getValue(
            'mw_dailydeal/group_general/active',
            ScopeInterface::SCOPE_STORE
        )) {
            return '';
        }

        $short_url_image = $this->helperConfig->getConfigIsShowImageCatalogList();
        $url_image = $this->storeManager
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'mw_dailydeal/'.$short_url_image;
        return $url_image;
    }

    public function renderDealQtyOnCatalogPage($qty)
    {
        $value = $this->helperConfig->getConfigDealQtyOnCatalogPage();
        $search = array('{{qty}}');
        $replace = array($qty);
        $html = str_replace($search, $replace, $value);
        return $html;
    }

    public function encodeData($data)
    {
        $version = $this->productMetadata->getVersion();
        $ver = explode(".", $version);
        if (($ver[0] == 2) && ($ver[1] < 2 )) {
            return $this->serializer->serialize($data);
        } else {
            return json_encode($data);
        }
    }

    public function decodeData($data)
    {
        $version = $this->productMetadata->getVersion();
        $ver = explode(".", $version);
        if (($ver[0] == 2) && ($ver[1] < 2 )) {
            return $this->serializer->unserialize($data);
        } else {
            return json_decode($data, true);
        }
    }

    public function sendNotifyNoDealEmail()
    {
        try {
            $sender = 'MW Daily Deal';
            $senderEmail = 'admin@admin.com';
            $receive = $this->helperConfig->getConfigAdminEmail();

            $name = "MW Daily Deal";
            $data['subject'] = 'Website don\'t have a deal';

            $templateEmail = $this->helperConfig->getConfigEmailTemplateNoDeal();
            $this->inlineTranslation->suspend();
            $sender = [
                'name' => $this->escaper->escapeHtml($sender),
                'email' => $this->escaper->escapeHtml($senderEmail),
            ];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateEmail)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars([
                    'subject'  => 'Website don\'t have a deal',
                ])
                ->setFrom($sender)
                ->addTo($receive)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    public static function reIndexArray(array &$array)
    {
        $temp = $array;
        $array = array();
        foreach ($temp as $key => $value) {
            $array[] = $value;
        }
    }

    public function getConfigSortBy()
    {
        return $this->helperConfig->getConfigSortBy();
    }

    public function loadOrderIncrementId($incrementId)
    {
        return $this->order->loadByIncrementId($incrementId);
    }

    public function chooseColumnLayout()
    {
        $columnNumber = $this->helperConfig->getConfigPageLayout();
        switch ($columnNumber) {
            case 'empty':
                return 'page/empty.phtml';
                break;
            case 'one_column':
                return 'page/1column.phtml';
                break;
            case 'two_columns_left':
                return 'page/2columns-left.phtml';
                break;
            case 'two_columns_right':
                return 'page/2columns-right.phtml';
                break;
            case 'three_columns':
                return 'page/3columns.phtml';
                break;
            default:
                return 'page/3columns.phtml';
                break;
        }
    }

    public function getProductDealling($dealId)
    {
        $productDeal = $this->dealSchedulerProduct->getCollection();
        $productDeal->addFieldToFilter('deal_scheduler_id', ['nin' => $dealId]);
        $productIds = array();
        foreach ($productDeal as $deal) {
            $productIds[] = $deal->getProductId();
        }
        return $productIds;
    }
}

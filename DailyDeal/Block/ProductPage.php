<?php
namespace MW\DailyDeal\Block;

use Magento\Store\Model\ScopeInterface;

class ProductPage extends \Magento\Framework\View\Element\Template
{
    protected $checkoutSession;
    protected $sessionManager;
    protected $dailydealModel;
    /**
     * @var \MW\DailyDeal\Model\ResourceModel\Dailydeal\CollectionFactory
     */
    protected $dailydealCollectionFactory;

    protected $registry;

    protected $helperConfig;

    protected $date;

    protected $business;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \MW\DailyDeal\Model\Dailydeal $dailydealModel,
        \MW\DailyDeal\Model\ResourceModel\Dailydeal\CollectionFactory $dailydealCollectionFactory,
        \Magento\Framework\Registry $registry,
        \MW\DailyDeal\Helper\Config $helperConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \MW\DailyDeal\Model\Business $business,
        array $data = []
    ) {

        $this->checkoutSession = $checkoutSession;
        $this->dailydealModel = $dailydealModel;
        $this->dailydealCollectionFactory = $dailydealCollectionFactory;
        $this->registry = $registry;
        $this->helperConfig = $helperConfig;
        $this->date = $date;
        $this->business = $business;
        parent::__construct($context, $data);
    }

    public function getDeals($_product)
    {
        $this->business->updateStatusDeals();
        if (!$this->helperConfig->isEnabled()) {
            return false;
        }
        $deal = $this->dailydealModel->loadActiveDealByProductId($_product->getId());
        return $deal;
    }

    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    public function getProductDetailLabel()
    {
        return $this->helperConfig->getConfigProductDetailLabel();
    }

    public function getTimeChange()
    {
        /** get time Change */
    }

    public function getConfigTodayDealShowDetail()
    {
        return $this->helperConfig->getConfigTodayDealShowDetail();
    }

    public function getConfigDisplayQuantity()
    {
        return $this->helperConfig->getConfigDisplayQuantity();
    }

    public function getConfigDisplayDescription()
    {
        return $this->helperConfig->getConfigDisplayDescription();
    }

    public function renderDealQtyOnProductPage($qty)
    {
        $value = $this->helperConfig->getConfigDealQtyOnProductPage();
        $search = array('{{qty}}');
        $replace = array($qty);
        $html = str_replace($search, $replace, $value);
        return $html;
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

    public function getCurrentTime()
    {
        return $this->date->scopeTimeStamp();
    }
}

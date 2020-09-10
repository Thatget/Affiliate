<?php

namespace MW\DailyDeal\Block\Sidebar;

use Magento\Store\Model\ScopeInterface;

class Calendar extends \Magento\Framework\View\Element\Template
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

    public $date;

    protected $resource;

    protected $asset;

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
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\View\Asset\Repository $asset,
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
        $this->date = $date;
        $this->resource = $resource;
        $this->asset = $asset;
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

    public function getConfigTodayDealBlockTitle()
    {
        return $this->helperConfig->getConfigTodayDealBlockTitle();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWeeklyDeal()
    {
        $store_id = $this->storeManager->getStore()->getId();
        $connection = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $tblCatalogStockItem = $connection->getTableName('cataloginventory_stock_item');
        $weeklydeal = [];
        $consecutive7days = [];
        $i = 0;
        $m = date('m', $this->date->scopeTimeStamp());
        $d = date('d', $this->date->scopeTimeStamp());
        $Y = date('Y', $this->date->scopeTimeStamp());
        while ($i < 7) {
            array_push($consecutive7days, date('Y-m-d', mktime(0, 0, 0, $m, $d + $i, $Y)));
            $i++;
        }
        $weeklyCollection = $this->dailydealModel->getCollection()
            ->addFieldToFilter('status', \MW\DailyDeal\Model\Status::STATUS_ENABLED)
            ->addFieldToFilter('expire', \MW\DailyDeal\Model\Status::STATUS_EXPIRE_FALSE)
            ->addFieldToFilter('store_view', array(array('like'=>'%'. $store_id .'%'), array('like'=>'0')))
            ->addProductStatusFilter($store_id)
            ->getConfigSortBy();

        $weeklyCollection->getSelect()->joinLeft(
            array('stock' => $tblCatalogStockItem),
            'stock.product_id = main_table.product_id',
            array('stock.qty', 'stock.is_in_stock')
        );

        $weeklyCollection->getSelect()
            ->where("stock.is_in_stock = " . \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK);

        // Remove deal end_date_time < current_date_time - begin
        $current_time = $this->date->scopeTimeStamp();
        if (count($weeklyCollection) > 0) {
            foreach ($weeklyCollection->getItems() as $key => $_deal) {
                $stat_time = strtotime($_deal->getData('start_date_time'));
                $end_time = strtotime($_deal->getData('end_date_time'));
                if ($end_time < $current_time) {
                    $weeklyCollection->removeItemByKey($key);
                }
            }
        }
        // Remove deal end_date_time < current_date_time - end

        if (count($weeklyCollection) > 0) {
            foreach ($weeklyCollection as $weekly) {
                $Ystart = date('Y', strtotime($weekly->getStartDateTime()));
                $mstart = date('m', strtotime($weekly->getStartDateTime()));
                $dstart = date('d', strtotime($weekly->getStartDateTime()));
                $daysdeal = (strtotime($weekly->getEndDateTime()) - strtotime($weekly->getStartDateTime())) / 86400;

                $formatDealEnd = date('Y-m-d', strtotime($weekly->getEndDateTime()));
                $seekday = '';
                // select in 7 day
                for ($j = 0; $j < $daysdeal + 1; $j++) {
                    $seekday = date('Y-m-d', mktime(0, 0, 0, $mstart, $dstart + $j, $Ystart));
                    if (in_array($seekday, $consecutive7days) && !in_array($seekday, $weeklydeal)) {
                        array_push($weeklydeal, $seekday);
                    }
                    if ($seekday == $formatDealEnd) {
                        break;
                    }
                }
            }
        }

        return $weeklydeal;
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

    /**
     * @param $days
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function renderDealFollowDayHtml($days)
    {
        $store_id = $this->storeManager->getStore()->getId();
        $connection  = $this->resource->getConnection();
        $tblCatalogStockItem = $connection->getTableName('cataloginventory_stock_item');
        $dayselect = $days;
        $Y = date('Y', $this->date->scopeTimeStamp());
        $startday = implode(array($dayselect, ' 00:00:00'));
        $startDayTime = strtotime($startday);
        $endday = implode(array($dayselect, ' 23:59:59'));
        $enddaytime = strtotime($endday);
        /* @var $deals \MW\DailyDeal\Model\ResourceModel\Dailydeal\Collection */
        $deals = $this->dailydealModel->getCollection()
            ->addFieldToFilter('status', \MW\DailyDeal\Model\Status::STATUS_ENABLED)
            ->addFieldToFilter('expire', \MW\DailyDeal\Model\Status::STATUS_EXPIRE_FALSE)
            ->addFieldToFilter('store_view', array(array('like'=>'%'. $store_id .'%'), array('like'=>'0')))
            ->addProductStatusFilter($store_id)
            ->getConfigSortBy();

        $deals->getSelect()->joinLeft(
            array('stock' => $tblCatalogStockItem),
            'stock.product_id = main_table.product_id',
            array('stock.qty', 'stock.is_in_stock')
        );
        $deals->getSelect()
            ->where("stock.is_in_stock = " . \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK);
        $block = $this->getLayout()->createBlock('MW\DailyDeal\Block\Sidebar\Deal')
            ->setData('deals', $deals)
            ->setData('startdaytime', $startDayTime)
            ->setData('enddaytime', $enddaytime)
            ->setTemplate('MW_DailyDeal::sidebar/calendar_product.phtml');
        $result = $block->toHtml();
        return  $result;
    }

    public function isShowWeekDeals()
    {
        if (!$this->helperConfig->isEnabled()) {
            return false;
        }
        return $this->helperConfig->getConfigShowWeekDealBlock();
    }
}

<?php
namespace MW\DailyDeal\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Profiler\Driver\Standard\Stat;
use MW\DailyDeal\Api\Data\DealInterface;
use MW\DailyDeal\Model\ProductDeal;

class Dailydeal extends AbstractModel implements DealInterface, IdentityInterface
{
    const CACHE_TAG = 'mw_dailydeal';
    const STATUS_ENABLED = '1';
    const STATUS_EXPIRE_FALSE = '1';
    const FEATURED_DEAL = '1';

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;
    protected $storeManager;
    protected $date;
    protected $dealSchedulerProduct;
    protected $productRepository;
    /**
     * [$_formFieldHtmlIdPrefix description].
     *
     * @var string
     */
    protected $_formFieldHtmlIdPrefix = 'page_';

    /**
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date
     * @param Dealschedulerproduct $dealSchedulerProduct
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \MW\DailyDeal\Model\Dealschedulerproduct $dealSchedulerProduct,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->pricingHelper = $pricingHelper;
        $this->storeManager = $storeManager;
        $this->date = $date;
        $this->dealSchedulerProduct = $dealSchedulerProduct;
        $this->productRepository = $productRepository;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('MW\DailyDeal\Model\ResourceModel\Dailydeal');
        $this->setIdFieldName('dailydeal_id');
    }
    /**
     * Return unique ID(s)
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::DAILYDEAL_ID);
    }

    /**
     * Set id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::DAILYDEAL_ID, $id);
    }

    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    public function getCurProduct()
    {
        return $this->getData(self::CUR_PRODUCT);
    }

    public function setCurProduct($curProduct)
    {
        return $this->setData(self::CUR_PRODUCT, $curProduct);
    }

    public function getProductSku()
    {
        return $this->getData(self::PRODUCT_SKU);
    }

    public function setProductSku($productSku)
    {
        return $this->setData(self::PRODUCT_SKU, $productSku);
    }

    public function getProductPrice()
    {
        return $this->getData(self::PRODUCT_PRICE);
    }

    public function setProductPrice($productPrice)
    {
        return $this->setData(self::PRODUCT_PRICE, $productPrice);
    }

    public function getDiscount()
    {
        return $this->getData(self::DISCOUNT);
    }

    public function setDiscount($discount)
    {
        return $this->setData(self::DISCOUNT, $discount);
    }

    public function getDiscountType()
    {
        return $this->getData(self::DISCOUNT_TYPE);
    }

    public function setDiscountType($discountType)
    {
        return $this->setData(self::DISCOUNT_TYPE, $discountType);
    }

    public function getStartDateTime()
    {
        return $this->getData(self::START_DATE_TIME);
    }

    public function setStartDateTime($startDateTime)
    {
        return $this->setData(self::START_DATE_TIME, $startDateTime);
    }

    public function getEndDateTime()
    {
        return $this->getData(self::END_DATE_TIME);
    }

    public function setEndDateTime($endDateTime)
    {
        return $this->setData(self::END_DATE_TIME, $endDateTime);
    }

    public function getSoldQty()
    {
        return $this->getData(self::SOLD_QTY);
    }

    public function setSoldQty($soldQty)
    {
        return $this->setData(self::SOLD_QTY, $soldQty);
    }

    public function getDealQty()
    {
        return $this->getData(self::DEAL_QTY);
    }

    public function setDealQty($dealQty)
    {
        return $this->setData(self::DEAL_QTY, $dealQty);
    }

    /**
     * true : allow show deal, false : not show deal
     * @return boolean
     */
    public function checkDealQtyToShow($model_product)
    {
        $return = false;
        if ($model_product->getData('type_id') == 'configurable' ||
            $model_product->getData('type_id') == 'grouped' ||
            $model_product->getData('type_id') == 'bundle') {
            // alow buy
            $return = false;
        }

        if ($model_product->getData('type_id') == 'simple' ||
            $model_product->getData('type_id') == 'virtual' ||
            $model_product->getData('type_id') == 'downloadable') {
            $dealqty = $this->getData('deal_qty');
            $soldqty = $this->getData('sold_qty');

            if ($dealqty == 0) {
                $return = true;
            }

            if ($dealqty - $soldqty > 0) {
                // alow buy
                $return = true;
            }
        }

        return true; //$return;
    }

    public function processValueDiscountSaveBought($param = array())
    {
        if (isset($param['model_product'])) {
            $model_product = $param['model_product'];
        } else {
            // load by product_id
            $model_product = $param['model_product'];
        }

        $product_price = round($model_product['price'], 2);
        $discount = $this->getData('discount');
        $you_save = $product_price * $discount / 100;
        $one_percent_price = $product_price / 100;
        if ($product_price == 0) {
            $data = [];
            $data['discount'] = $this->getDiscount() . '%';
            $data['bought'] = $this->getData('sold_qty');
            $this->setData('value_discount_save_bought', $data);
        } else {
            $percent = $discount; //round(($product_price - $deal_price ) / $one_percent_price, 2);
            if ($this->getId()) {
                $data = [];
                $data['discount'] = $percent . '%';
                $data['you_save'] = $this->pricingHelper->currency($you_save, true, false);
                $data['bought'] = $this->getData('sold_qty');
                $this->setData('value_discount_save_bought', $data);
            }
        }
    }

    /**
     * get deal running
     * @param int $product_id
     * @return bool or model
     */
    public function loadByProductId($product_id = null)
    {
//        $now = $this->date->date()->format('Y-m-d H:i:s');

        $collection_deal = $this->getCollection()
            ->addFieldToFilter('product_id', $product_id)
            ->addFieldToFilter('status', ['nin' => [Status::STATUS_TIME_DISABLED, Status::STATUS_TIME_QUEUED, Status::STATUS_TIME_ENDED]])
//            ->addFieldToFilter('expire', self::STATUS_EXPIRE_FALSE)
//            ->addFieldToFilter('store_view', array(array('like' => '%' . $this->getStore()->getId() . '%'), array('like' => '0')))
//            ->addFieldToFilter('start_date_time', array('to' => $now))
//            ->addFieldToFilter('end_date_time', array('from' => $now))
            ->load();

        if (count($collection_deal->getItems())) {
            $this->setData($collection_deal->getFirstItem()->getData());
        } else {
            return false;
        }

        return $this;
    }

    /**
     * get deal running
     * @param int $product_id
     * @return Dailydeal
     */
    public function loadPastDealByProductId($product_id)
    {
        $now = $this->date->date()->format('Y-m-d H:i:s');

        $collection_deal = $this->getCollection()
            ->addFieldToFilter('product_id', $product_id)
            ->addFieldToFilter('status', Status::STATUS_TIME_ENDED)
//            ->addFieldToFilter('expire', self::STATUS_EXPIRE_FALSE)
//            ->addFieldToFilter('store_view', array(array('like' => '%' . $this->getStore()->getId() . '%'), array('like' => '0')))
//            ->addFieldToFilter('end_date_time', array('to' => $now))
            ->load();

        if (count($collection_deal->getItems())) {
            $this->setData($collection_deal->getFirstItem()->getData());
        }
        return $this;
    }

    /**
     * get deal running
     * @param int $product_id
     * @return bool or model
     */
    public function loadActiveDealByProductId($product_id = null)
    {
        $collection_deal = $this->getCollection()
            ->addFieldToFilter('product_id', $product_id)
            ->addFieldToFilter('status', Status::STATUS_TIME_RUNNING)
//            ->addFieldToFilter('store_view', array(
//                array('like' => '%' . $this->getStore()->getId() . '%'), array('like' => '0')
//            ))
//            ->addFieldToFilter('start_date_time', array('to' => $now))
//            ->addFieldToFilter('end_date_time', array('from' => $now))
            ->load();

        if (count($collection_deal->getItems())) {
            $this->setData($collection_deal->getFirstItem()->getData());
            return $collection_deal->getFirstItem();
        } else {
            return false;
        }
    }

    public function checkDealQty($model_product, $model_deal)
    {
        $return = false;
        if ($model_product->getData('type_id') == Status::TYPE_CONFIGURABLE ||
            $model_product->getData('type_id') == Status::TYPE_GROUPED ||
            $model_product->getData('type_id') == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            // alow buy
            $return = true;
        }

        if ($model_product->getData('type_id') == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE ||
            $model_product->getData('type_id') == \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL ||
            $model_product->getData('type_id') == Status::TYPE_DOWNLOADABLE) {
            $dealQty = $model_deal->getData('deal_qty');
            $soldQty = $model_deal->getData('sold_qty');

            if ($dealQty == 0) {
                $return = true;
            }

            if ($dealQty - $soldQty > 0) {
                // alow buy
                $return = true;
            }
        }
        return $return;
    }

    /**
     * true : allow view price, false : not view price
     * @param $model_product
     * @return boolean
     */
    public function checkDealPrice($model_product)
    {
        $return = true;
        if ($model_product->getData('type_id') == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE ||
            $model_product->getData('type_id') == Status::TYPE_CONFIGURABLE) {
            // not alow view price
            $return = false;
        }
        return $return;
    }

    public function getActiveDeals()
    {
        $now = $this->date->date()->format('Y-m-d H:i:s');

        $collection_deal = $this->getCollection()
            ->addFieldToFilter('status', Status::STATUS_TIME_RUNNING)
            ->addFieldToFilter('store_view', array(array('like' => '%' . $this->getStore()->getId() . '%'), array('like' => '0')))
            ->addFieldToFilter('start_date_time', array('to' => $now))
            ->addFieldToFilter('end_date_time', array('from' => $now))
            ->getConfigSortBy()
            ->load();
        return $collection_deal;
    }

    public function getFeaturedDeals()
    {
        $collection_deal = $this->getCollection()
            ->addFieldToFilter('status', Status::STATUS_TIME_RUNNING)
            ->addFieldToFilter('featured', Status::STATUS_FEATURED_ENABLED)
            ->load();
        return $collection_deal;
    }

    public function getListActiveDeal()
    {
        $collection_deal = $this->getCollection()
            ->addFieldToFilter('status', Status::STATUS_TIME_RUNNING)
//            ->addFieldToFilter('expire', self::STATUS_EXPIRE_FALSE)
//            ->addFieldToFilter('store_view', array(array('like' => '%' . $this->getStore()->getId() . '%'), array('like' => '0')))
//            ->addFieldToFilter('start_date_time', array('to' => $now))
//            ->addFieldToFilter('end_date_time', array('from' => $now))
            ->load();
        $listProductId = [];
        foreach ($collection_deal as $deal) {
            $storeView = $deal->getStoreView();
            $storeViewArray = explode(",", $storeView);
            $storeId = $this->getStore()->getId();
            if (in_array($storeId, $storeViewArray)) {
                $listProductId[] += $deal->getProductId();
            }
        }

        return array_unique($listProductId);
    }

    public function getListPastDeal()
    {
        $now = $this->date->scopeTimeStamp();
        $collection_deal = $this->getCollection()
            ->addFieldToFilter('status', Status::STATUS_TIME_ENDED)
//            ->addFieldToFilter('expire', self::STATUS_EXPIRE_FALSE)
//            ->addFieldToFilter('store_view', ['eq' => $this->getStore()->getId()])
//            ->addFieldToFilter('start_date_time', array('to' => $now))
//            ->addFieldToFilter('end_date_time', array('from' => $now))
            ->load();
//        $collection_deal->getSelect()->where("
//                    (end_date_time <= '{$currentTime}') OR (((expire = '". self::STATUS_EXPIRE_FALSE . "')))
//                ");
        $listProductId = [];
        foreach ($collection_deal as $deal) {
            if (strtotime($deal->getEndDateTime()) <= $now) {
                $storeView = $deal->getStoreView();
                $storeViewArray = explode(",", $storeView);
                $storeId = $this->getStore()->getId();
                $flagStore = false;
                if (in_array($storeId, $storeViewArray)) {
                    $flagStore = true;
                }
                $flagDeal = false;
                if ($deal->getData('deal_scheduler_id') > 0) {
                    $dealSchedulerId = $deal->getData('deal_scheduler_id');
                    $collection = $this->getCollection()
                        ->addFieldToFilter('status', Status::STATUS_TIME_RUNNING)
                        ->addFieldToFilter('deal_scheduler_id', $dealSchedulerId);
                    if (count($collection) > 0) {
                        $flagDeal = true;
                    }
                }
                if ($flagStore && !$flagDeal) {
                    $listProductId[] += $deal->getProductId();
                }
            }
        }

        return $listProductId;
    }

    public function getListCommingDeal()
    {
        $collection_deal = $this->getCollection()
            ->addFieldToFilter('status', Status::STATUS_TIME_QUEUED)
//            ->addFieldToFilter('store_view', array(array('like' => '%' . $storeId . '%'), array('like' => '0')))
            ->load();
//        $collection_deal->getSelect()->where("
//                    (end_date_time <= '{$currentTime}') OR (((expire = '". self::STATUS_EXPIRE_FALSE . "')))
//                ");
        $listProductId = [];
        foreach ($collection_deal as $deal) {
            $storeView = $deal->getStoreView();
            $storeViewArray = explode(",", $storeView);
            $storeId = $this->getStore()->getId();
            $result = false;
            if ($deal->getData('deal_scheduler_id') > 0) {
                $result = $this->checkSchedulerRunning($deal->getData('deal_scheduler_id'));
            }
            if (in_array($storeId, $storeViewArray) && !$result) {
                $listProductId[] += $deal->getProductId();
            }
        }

        return $listProductId;
    }

    public function checkSchedulerRunning($dealSchedulerId)
    {
        $productIds = array_keys($this->dealSchedulerProduct->getProductOptionArray($dealSchedulerId));
        foreach ($productIds as $productId) {
            $col = $this->getCollection()
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('status', Status::STATUS_TIME_RUNNING);
            if (count($col) > 0) {
                return true;
            }
            return false;
        }
    }
    public function getStore()
    {
        $store = $this->storeManager->getStore();
        return $store;
    }

    public function getCommingDealByProductId($productId)
    {
//        $now = time();
//        $currentTime = date('Y-m-d', $this->date->scopeTimeStamp());
        $collection_deal = $this->getCollection()
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('status', Status::STATUS_TIME_QUEUED)
            ->addFieldToFilter('store_view', array(array('like' => '%' . $this->getStore()->getId() . '%'), array('like' => '0')))
//            ->addFieldToFilter('start_date_time', array('from' => $now))
            ->load();
        if (count($collection_deal->getItems())) {
            $this->setData($collection_deal->getFirstItem()->getData());
            return $collection_deal->getFirstItem();
        } else {
            return $this;
        }
    }

    /**
     * true : allow buy, false : not buy
     * @param $model_product
     * @param $model_deal
     * @param $buy_qty
     * @return boolean
     */
    public function checkSoldQty($model_product, $model_deal, $buy_qty)
    {
        $return = false;
        if ($model_product->getData('type_id') == \MW\DailyDeal\Model\Status::TYPE_CONFIGURABLE ||
            $model_product->getData('type_id') == \MW\DailyDeal\Model\Status::TYPE_GROUPED ||
            $model_product->getData('type_id') == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            // alow buy
            $return = true;
        }

        if ($model_product->getData('type_id') == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE ||
            $model_product->getData('type_id') == \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL ||
            $model_product->getData('type_id') == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            $dealqty = $model_deal->getData('deal_qty');
            $soldqty = $model_deal->getData('sold_qty');

            if ($dealqty == 0) {
                $return = true;
            }

            if ($buy_qty <= ($dealqty - $soldqty)) {
                // alow buy
                $return = true;
            }
        }

        return $return;
    }

    /**
     * @param $order_id
     * @return $this
     */
    public function insertOrderId($order_id)
    {
        $temp_order_ids = $this->getData('order_id');
//        $temp_order_ids[] = $order_id;
        if ($temp_order_ids == null) {
            $order_ids = $order_id;
        } else {
            $order_ids = $temp_order_ids . ',' . $order_id;
        }
//        $order_ids = array_filter($temp_order_ids);
        $this->setData('order_id', $order_ids);

        return $this;
    }

    /**
     * @param $model_product
     * @param $activeDeal
     * @param string $column
     */
    public function setMinPriceFollowProductType($model_product, $activeDeal)
    {
        $column = 'min_price';
        if ($model_product->getData('type_id') == \MW\DailyDeal\Model\Status::TYPE_GROUPED) {
            $price = ProductDeal::getMinPriceProductGrouped($model_product);
            $model_product->setData($column, $price);
        } elseif ($model_product->getData('type_id') == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            $price = ProductDeal::getMinPriceProductBundle($model_product);
            $model_product->setData($column, $price);
        } elseif ($model_product->getData('type_id') == Status::TYPE_CONFIGURABLE) {
//            $price = ProductDeal::getMinPriceProductConfig($model_product);
//            $regularPrice = $model_product->getPriceInfo()->getPrice('regular_price');
//            $min_price = $regularPrice->getMinRegularAmount(); //.'-'.$regularPrice->getMaxRegularAmount();
            $price = $model_product->getData('price');
            //$model_product->getPriceInfo()->getPrice('regular_price')->getminRegularAmount()->getValue();
            $discount = $activeDeal->getDiscount();
            $minPrice = $price * (100 - $discount) / 100;
            $model_product->setData($column, $minPrice);
        }
    }

    /**
     * Check : next many days,  system have deal : queue, running, ended
     * @param $condition
     * @return bool
     */
    public function isHaveDealRunning($condition)
    {
        $result = false;

        $collection = $this->getCollection()
            ->addFieldToFilter('status', \MW\DailyDeal\Model\Status::STATUS_ENABLED);

        if (isset($condition['now']) && isset($condition['end_date_time'])) {
            $collection->addFieldToFilter('end_date_time', array('from' => $condition['now']));
        }

        if ($collection->count()) {
            $result = true;
        }

        return $result;
    }

    public function getLimitStartDateTime($deal_scheduler_id, $index)
    {
        $result = '';
        $collection = $this->getCollection()
            ->addFieldToFilter('status', Status::STATUS_ENABLED)
            ->addFieldToFilter('deal_scheduler_id', $deal_scheduler_id)
            ->addFieldToFilter('thread', $index)
            ->addAttributeToSort('end_date_time', 'DESC');

        if (count($collection->getItems())) {
            $result = $collection->getFirstItem()->getData('end_date_time');
        }

        return $result;
    }

    /**
     * get form field html id prefix.
     *
     * @return string
     */
    public function getFormFieldHtmlIdPrefix()
    {
        return $this->_formFieldHtmlIdPrefix;
    }

    /**
     * get store attributes.
     *
     * @return array
     */
    public static function getStoreAttributes()
    {
        return array(
            'name',
            'status',
        );
    }

    /**
     * @return array
     */
    public function getProductDealIds($dealId)
    {
        $productDeal = $this->getCollection();
        if ($dealId != null) {
            $productDeal->addFieldToFilter('dailydeal_id', ['neq' => $dealId])
            ->addFieldToFilter('product_id', ['neq' => $this->load($dealId)->getProductId()]);
        }

        $productIds = [];
        $productDeal->addFieldToFilter('status', ['in'=>[Status::STATUS_TIME_QUEUED, Status::STATUS_TIME_RUNNING]]);
//            ->addFieldToFilter('deal_scheduler_id', ['eq' => 0]);
        foreach ($productDeal as $deal) {
            $productIds[] = $deal->getProductId();
        }
        return $productIds;
    }

    protected function _beforeSave()
    {
//        $this->validate();
//        $this->setDataDefault();

        $id = $this->getRequest()->getParam('dailydeal_id');
        if (isset($id)) {
//            $this->setDataFollowOldData();
//            $this->setDisableProduct();
        }

//        $this->setExpire();
        $this->autoSetActive();
        $this->convertStoreViewToString();
        $this->convertOrderIdToString();

        parent::beforeSave();
    }

    /**
     * Get status active of this (deal).
     * @return int
     */
    public function getStatusTime()
    {
        $model_deal = $this;
        $timestamp = $this->date->scopeTimeStamp();
        $start_date_time = strtotime($model_deal->getData('start_date_time'));
        $end_date_time = strtotime($model_deal->getData('end_date_time'));
        $expire = $model_deal->getData('expire');

        // 1 DISABLE
        if ($model_deal->getData('status') == Status::STATUS_TIME_DISABLED) {
            return Status::STATUS_TIME_DISABLED;
        } elseif ($start_date_time <= $timestamp and $timestamp <= $end_date_time and $expire == Status::STATUS_EXPIRE_FALSE) { // 2 ENABLE, RUNNING
            return Status::STATUS_TIME_RUNNING;
        } elseif ($end_date_time < $timestamp || $expire == Status::STATUS_EXPIRE_TRUE) { // 3 ENDED
            return Status::STATUS_TIME_ENDED;
        }

        // 4 QUEUED
        if ($timestamp < $start_date_time) {
            return Status::STATUS_TIME_QUEUED;
        }

        // Default
        return Status::STATUS_TIME_DISABLED;
    }

    /**
     * auto set active of deal => to view color
     */
    protected function autoSetActive()
    {
        $this->setData('active', $this->getStatusTime());
        $this->setData('status', $this->getStatusTime());
    }

    protected function convertStoreViewToString()
    {
        $str_store_view = '';
        $data = $this->getData();
        $store_view = $data['store_view'];
        if (is_array($store_view)) {
            if (in_array('0', $store_view)) {
                $str_store_view = 0;
            } else {
                $str_store_view = implode(',', $store_view);
            }
        }
        return $str_store_view;
    }

    protected function convertOrderIdToString()
    {
        $data = $this->getData();
        if (isset($data['order_id'])) {
            $this->setData('order_id', $data['order_id']);
        }
    }

    public function checkDealQtyToEndDeal($sold_qty)
    {
        $dealQty = $this->getDealQty();
        if (($dealQty != 0) && ($sold_qty >= $dealQty)) {
            if ($this->getData('disable_product_after_finish') == Status::STATUS_PRODUCT_ENABLED) {
                $productId = $this->getData('product_id');
                $product = $this->productRepository->getById($productId);
                $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                $this->productRepository->save($product);
            }
            $this->setData('status', Status::STATUS_TIME_ENDED)->save();
        }
    }
}

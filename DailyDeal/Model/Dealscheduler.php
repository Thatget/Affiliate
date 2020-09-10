<?php
namespace MW\DailyDeal\Model;

class Dealscheduler extends \Magento\Framework\Model\AbstractModel
{
    protected $productloader;
    protected $dailyDealModel;
    protected $date;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ProductFactory $productloader
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $productloader,
        \MW\DailyDeal\Model\Dailydeal $dailyDealModel,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productloader = $productloader;
        $this->dailyDealModel = $dailyDealModel;
        $this->date = $date;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('MW\DailyDeal\Model\ResourceModel\Dealscheduler');
        $this->setIdFieldName('deal_scheduler_id');
    }

    public function generalDeal($product, $index, $thread_start_date_time, $to_time)
    {
        try {
            $model_product = $this->productloader->create()->load($product['product_id']);
            $model_deal = $this->dailyDealModel;

            $data_default = [];
            $data_default['product_id'] = $product['product_id'];
            $data_default['product_sku'] = $model_product->getData('sku');
            $data_default['product_price'] = $model_product->getData('price');
            $data_default['cur_product'] = $model_product->getData('name');
            $data_default['deal_scheduler_id'] = $this->getId();
            $data_default['store_view'] = implode(',', $model_product->getStoreIds());
            $data_default['description'] = $this->getData('title');
            $data_default['sold_qty'] = '';
            $data_default['limit_customer'] = 0;
            $data_default['featured'] = Status::STATUS_FEATURED_DISABLED;
            $data_default['disable_product_after_finish'] = Status::STATUS_PRODUCT_DISABLED;
            $data_default['status'] = Status::STATUS_ENABLED;
            $data_default['thread'] = $index;

            if (($product['deal_price']) != '') {
                $data_default['discount'] = $product['deal_price'];
            } else {
                $data_default['discount'] = $this->getDealPrice();
            }
            $data_default['dailydeal_price'] = $model_product->getData('price') * (100 - $data_default['discount']) / 100;
            if (($product['product_deal_qty']) > 0) {
                $data_default['deal_qty'] = $product['product_deal_qty'];
            } else {
                $data_default['deal_qty'] = $this->getDealQty();
            }
            if (($product['deal_time']) != '') {
                $data_default['deal_time'] = $product['deal_time'];
            } else {
                $data_default['deal_time'] = $this->getDealTime();
            }
            $start_date_time = $this->getThreadStartDateTime($index, $thread_start_date_time);
            $end_date_time = \MW\DailyDeal\Helper\Toolasiaconnect::increaseTime(
                $start_date_time,
                1,
                $data_default['deal_time'],
                'Y-m-d H:i:s'
            );
            $end_date_time = $this->getLimitEndDateTime($end_date_time);
            if (strtotime(substr($start_date_time, 0, -3)) == strtotime(substr($end_date_time, 0, -3))) {
                $success = false;
                return array($success, $end_date_time);
            }
            $data_default['start_date_time'] = $start_date_time;
            $data_default['end_date_time'] = $end_date_time;
            $data_default['status'] = $this->getStatusByTime($start_date_time, $end_date_time);
            $model_deal->setData($data_default);
            $model_deal->save();
            $success = true;
        } catch (\Exception $exc) {
            $success = false;
            $end_date_time = null;
        }

        return array($success, $end_date_time);
    }

    /**
     * Get status active of this (deal).
     * @param $start_date_time
     * @param $end_date_time
     * @return int
     */
    public function getStatusByTime($start_date_time, $end_date_time)
    {
        $timestamp = $this->date->scopeTimeStamp();
        $start_date_time = strtotime($start_date_time);
        $end_date_time = strtotime($end_date_time);

        // 1 DISABLE
        if ($this->getData('status') == Status::STATUS_TIME_DISABLED) {
            return Status::STATUS_TIME_DISABLED;
        } elseif ($start_date_time <= $timestamp and $timestamp <= $end_date_time) { // 2 ENABLE, RUNNING
            return Status::STATUS_TIME_RUNNING;
        } elseif ($end_date_time < $timestamp) {  // 3 ENDED
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
     * Start time of deal ( process follow 'end_date_time' deal )
     * @param $index
     * @param $thread_start_date_time
     * @return mixed
     */
    public function getThreadStartDateTime($index, $thread_start_date_time)
    {
        $data = [];

        if (empty($thread_start_date_time)) {
            $data[$index] = $this->getData('start_date_time');
        } else {
            $data[$index] = $thread_start_date_time;
        }

        $now_time = strtotime($this->date->date()->format('Y-m-d H:i:s'));
        $data_time = strtotime($data[$index]);

        if ($now_time >= $data_time) {
            $data[$index] = $this->date->date()->format('Y-m-d H:i:s');
        } elseif ($data_time > $now_time) {
            $data[$index] = \MW\DailyDeal\Helper\Toolasiaconnect::increaseTime($data[$index], 3, 1, 'Y-m-d H:i:s');
        }

        return $data[$index];
    }

    /**
     * End time of deal
     */
    public function getLimitEndDateTime($to)
    {
        $end_time = strtotime($this->getData('end_date_time'));
        $to_time = strtotime($to);

        if ($to_time > $end_time) {
            $data_time = $this->getData('end_date_time');
        } else {
            $data_time = $to;
        }

        return $data_time;
    }
}

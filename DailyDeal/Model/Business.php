<?php

namespace MW\DailyDeal\Model;

class Business extends \Magento\Framework\Model\AbstractModel
{
    protected $dailyDealModel;
    protected $productDealModel;
    protected $productRepository;
    protected $logger;
    protected $helperConfig;
    protected $helperDailyDeal;
    protected $dealSchedulerModel;
    protected $dealSchedulerProductModel;
    protected $date;
    protected $coreSession;
    protected $toolasiaconnect;

    /**
     * @param \MW\DailyDeal\Model\Dailydeal $dailyDealModel
     * @param \MW\DailyDeal\Model\ProductDeal $productDealModel
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Psr\Log\LoggerInterface $logger
     * @param \MW\DailyDeal\Helper\Config $helperConfig
     * @param \MW\DailyDeal\Helper\Data $helperDailyDeal
     * @param \MW\DailyDeal\Model\Dealscheduler $dealSchedulerModel
     * @param Dealschedulerproduct $dealSchedulerProductModel
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \MW\DailyDeal\Helper\ToolAsiaConnect $toolasiaconnect
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \MW\DailyDeal\Model\Dailydeal $dailyDealModel,
        \MW\DailyDeal\Model\ProductDeal $productDealModel,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger,
        \MW\DailyDeal\Helper\Config $helperConfig,
        \MW\DailyDeal\Helper\Data $helperDailyDeal,
        \MW\DailyDeal\Model\Dealscheduler $dealSchedulerModel,
        \MW\DailyDeal\Model\Dealschedulerproduct $dealSchedulerProductModel,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \MW\DailyDeal\Helper\ToolAsiaConnect $toolasiaconnect,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->dailyDealModel = $dailyDealModel;
        $this->productDealModel = $productDealModel;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->helperConfig = $helperConfig;
        $this->helperDailyDeal = $helperDailyDeal;
        $this->dealSchedulerModel = $dealSchedulerModel;
        $this->dealSchedulerProductModel = $dealSchedulerProductModel;
        $this->date = $date;
        $this->coreSession = $coreSession;
        $this->toolasiaconnect = $toolasiaconnect;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function autoGenerateDeal()
    {
        $now = date('Y-m-d H:i:s', $this->date->scopeTimeStamp());

        $collection = $this->dealSchedulerModel->getCollection()
            ->addFieldToFilter('status', Status::STATUS_ENABLED)
            ->addFieldToFilter('start_date_time', array('to' => $now))
            ->addFieldToFilter('end_date_time', array('from' => $now));

        foreach ($collection as $model) {
            $this->generalDeal($model->getId());
        }
    }

    /**
     * @param $deal_scheduler_id
     * @return int
     */
    public function generalDeal($deal_scheduler_id)
    {
        $model_deal_scheduler = $this->dealSchedulerModel->load($deal_scheduler_id);
        $model_product_scheduler = $this->dealSchedulerProductModel;
        $model_deal = $this->dailyDealModel;

        $products = $model_product_scheduler->getProductOptionArray($deal_scheduler_id);
        $products_sort = $model_product_scheduler->sortProductOptionArray(
            $products,
            $model_deal_scheduler->getData('generate_type')
        );

        $theads = $model_deal_scheduler->getData('number_deal');

        $count_generate_deal = 0;
        $now_time = $this->date->date()->format('Y-m-d H:i:s');
        $to_time = $this->toolasiaconnect->increaseTime(
            $now_time,
            5,
            $model_deal_scheduler->getData('number_day'),
            'Y-m-d H:i:s'
        );

        if ($model_deal_scheduler->getStatus() != Status::STATUS_TIME_RUNNING) {
            // Not Running, not active
            return $count_generate_deal;
        }

        // Default value
        $thread_start_date_time = [];
        $thread_have_time = [];
        for ($i = 0; $i < $theads; $i++) {
            $thread_start_date_time[$i] = $model_deal->getLimitStartDateTime($deal_scheduler_id, $i);
            $thread_number_product_error[$i] = 0;
            if (($thread_start_date_time[$i] != '') || (strtotime($thread_start_date_time[$i]) >= strtotime($to_time))) {
                $thread_have_time[$i] = false;  // Stop thread
            } else {
                $thread_have_time[$i] = true;
            }
        }
        $number_product = count($products);

        while (in_array(true, $thread_have_time) && $number_product > 0) {   // Have time and product
            for ($i = 0; $i < $theads; $i++) {   // Muity Thread Deal
                if (!$thread_have_time[$i]) {
                    continue;
                }

                if ((strtotime($thread_start_date_time[$i]) < strtotime($to_time))) {
                    list ($success, $end_date_time) = $model_deal_scheduler->generalDeal(
                        array_shift($products_sort),
                        $i,
                        $thread_start_date_time[$i],
                        $to_time
                    );
                }

                if ($success) {
                    $count_generate_deal++;
                    $thread_start_date_time[$i] = $end_date_time;
                    $thread_number_product_error[$i] = 0;
                } else {
                    $thread_number_product_error[$i]++;
                }

                if (strtotime($thread_start_date_time[$i]) >= strtotime($to_time)) {
                    $thread_have_time[$i] = false;  // Stop thread because thread not have time
                }

                if ($number_product <= $thread_number_product_error[$i]) {
                    $thread_have_time[$i] = false;  // Stop thread because thread not have product
                }

                if (empty($products_sort)) {
                    $products_sort = $model_product_scheduler->sortProductOptionArray(
                        $products,
                        $model_deal_scheduler->getData('generate_type')
                    );
                }

                if (!$success) {
                    $i--;   // current thread run again, choice other product because product is generated error,
                }

                // Limit number of deal because action generate in small time, server is not die.
                if ($count_generate_deal == Status::DEAL_SCHEDULER_GENERATE_LIMIT_AMOUNT) {
                    return $count_generate_deal;
                }
            }
        }

        return $count_generate_deal;
    }

    public function autoSendMail()
    {
        $result = 0;
        $now = date('Y-m-d 0:0:1', $this->date->scopeTimeStamp());
        $tomorrow = \MW\DailyDeal\Helper\ToolAsiaConnect::increaseTime($now, 5, 1, 'Y-m-d H:i:s');

        $condition = array(
            'end_date_time' => true,
            'now' => $tomorrow,
        );

        $flag_send_mail_on = $this->helperConfig->getConfigNotifyAdmin();
        $flag_have_deal = $this->dailyDealModel->isHaveDealRunning($condition);
        $flag_send_mail = $this->helperConfig->getConfigAdminEmail();

        if ($flag_send_mail_on) {
            if (!$flag_have_deal && $flag_send_mail) {
                // Not deal, send mail
                $this->sendMailAdminNotification();
                $result = 1;
            }
        }

        return $result;
    }

    public function sendMailAdminNotification()
    {
        $flag_send_mail_on = $this->helperConfig->getConfigNotifyAdmin();
        if (!$flag_send_mail_on) {
            return;
        }
        $this->helperDailyDeal->sendNotifyNoDealEmail();
    }

    public function autoDisableProduct()
    {
        $now = $this->date->date()->format('Y-m-d H:i:s');

        $collection_deal = $this->dailyDealModel->getCollection()
            ->addFieldToFilter('status', Status::STATUS_ENABLED)
            ->addFieldToFilter('disable_product_after_finish', Status::STATUS_PRODUCT_ENABLED)
            ->addFieldToFilter('end_date_time', array('to' => $now))
            ->load();

        foreach ($collection_deal as $model_deal) {
            $productId = $model_deal->getData('product_id');
            $product = $this->productRepository->getById($productId);
            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
            $this->productRepository->save($product);
            $model_deal->setData('disable_product_after_finish', Status::STATUS_PRODUCT_DONE);
            $model_deal->setData('status', Status::STATUS_TIME_ENDED);
            $model_deal->save();
        }
    }

    public function increateFeaturedView($deal_id)
    {
        $model_deal = $this->dailyDealModel->load($deal_id);
        $model_session = $this->coreSession;
        $session_deal_id_views = $model_session->getData('mw_dailydeal_featured_view_array');
        $session_deal_id_views = ($session_deal_id_views == null) ? [] : $session_deal_id_views;

        $visitor_data = $model_session->getData('visitor_data');

        // Have one action : back end, admin access to deal also increase view => Back end not running
        if ($visitor_data == null) {
            return;
        }

        if ($model_deal->getId()) {
            if (in_array($model_deal->getId(), $session_deal_id_views)) {
                // Customer has already viewed this deal
            } else {
                // Customer yet view this deal -> increase view
                $model_deal->setData('impression', $model_deal->getData('impression') + 1);
                $model_deal->save();

                $session_deal_id_views[] = $model_deal->getId();
                $model_session->setData('mw_dailydeal_featured_view_array', $session_deal_id_views);
            }
        }
    }

    public function updateStatusDeals()
    {
        $now = $this->date->date()->format('Y-m-d H:i:s');

        $collection_deal_start = $this->dailyDealModel->getCollection()
        ->addFieldToFilter('status', Status::STATUS_TIME_QUEUED)
        ->addFieldToFilter('start_date_time', array('to' => $now))
        ->addFieldToFilter('end_date_time', array('from' => $now))
        ->load();

        foreach ($collection_deal_start as $model_deal) {
            $model_deal->setData('status', Status::STATUS_TIME_RUNNING);
            $model_deal->save();
        }

        $collection_deal_end = $this->dailyDealModel->getCollection()
            ->addFieldToFilter('status', ['in' => [Status::STATUS_TIME_RUNNING, Status::STATUS_TIME_QUEUED]])
            ->addFieldToFilter('end_date_time', array('to' => $now))
            ->load();

        foreach ($collection_deal_end as $model_deal) {
            $model_deal->setData('status', Status::STATUS_TIME_ENDED);
            $model_deal->save();
        }
    }
}
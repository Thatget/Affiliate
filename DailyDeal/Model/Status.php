<?php
namespace MW\DailyDeal\Model;

class Status
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;

//    const DEAL_QUEUED = 0;
//    const DEAL_RUNNING = 1;
//    const DEAL_ENDED = 2;
//    const DEAL_DISABLE = 3;
    /**
     * Status
     */
    public function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled'),
        );
    }

    const STATUS_PRODUCT_ENABLED = 1;
    const STATUS_PRODUCT_DISABLED = 0;
    const STATUS_PRODUCT_DONE = 2;

    /**
     * Disable product after deal ends
     */
    public function getProductOptionArray()
    {
        return array(
            self::STATUS_PRODUCT_ENABLED => __('Yes'),
            self::STATUS_PRODUCT_DISABLED => __('No'),
        );
    }

    const STATUS_FEATURED_ENABLED = 1;
    const STATUS_FEATURED_DISABLED = 2;

    /**
     * Featured
     */
    public function getFeaturedOptionArray()
    {
        return array(
            self::STATUS_FEATURED_ENABLED => __('Yes'),
            self::STATUS_FEATURED_DISABLED => __('No'),
        );
    }

    const STATUS_TIME_QUEUED = 0;
    const STATUS_TIME_RUNNING = 1;
    const STATUS_TIME_DISABLED = 2;
    const STATUS_TIME_ENDED = 3;

    /**
     * @return array
     */
    public function getStatusTimeOptionArray()
    {
        return array(
            self::STATUS_TIME_QUEUED => __('Queued'),
            self::STATUS_TIME_RUNNING => __('Running'),
            self::STATUS_TIME_ENDED => __('Ended'),
            self::STATUS_TIME_DISABLED => __('Disabled'),
        );
    }

    const STATUS_EXPIRE_TRUE = 1;
    const STATUS_EXPIRE_FALSE = 0;

    const DEAL_SCHEDULER_GENERATE_TYPE_RANDOMLY = 0;
    const DEAL_SCHEDULER_GENERATE_TYPE_ROTATORS = 1;

    /**
     * Generation Type
     */
    public function getDealSchedulerGenerateTypeOptionArray()
    {
        return array(
            self::DEAL_SCHEDULER_GENERATE_TYPE_RANDOMLY => __('At Random'),
            self::DEAL_SCHEDULER_GENERATE_TYPE_ROTATORS => __('Sequentially'),
        );
    }

    const DEAL_SCHEDULER_GENERATE_LIMIT_AMOUNT = 200;

    const PRODUCT_HAVE_DEAL_ACTIVE = 1;
    const PRODUCT_HAVE_DEAL_ENDED = 3;

    /**
     * Mail
     */
    const SEND_MAIL_ADMIN_NOTIFICATION_DISABLE = 0;
    const SEND_MAIL_ADMIN_NOTIFICATION_ENABLE = 1;

    const TYPE_SIMPLE = 'simple';

    const TYPE_BUNDLE = 'bundle';

    const TYPE_VIRTUAL = 'virtual';

    const TYPE_GROUPED = 'grouped';

    const TYPE_CONFIGURABLE = 'configurable';

    const TYPE_DOWNLOADABLE = 'downloadable';
}

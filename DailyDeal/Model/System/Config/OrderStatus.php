<?php

namespace MW\DailyDeal\Model\System\Config;

class OrderStatus implements \Magento\Framework\Data\OptionSourceInterface
{
    const STATE_PENDING = 'pending';
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [];
        foreach (self::getOptionHash() as $value => $label) {
            $options[] = array(
                'value'    => $value,
                'label'    => $label
            );
        }
        return $options;
    }

    /**
     * get available statuses.
     *
     * @return []
     */
    public static function getOptionHash()
    {
        return [
            self::STATE_PENDING => __('Pending'),
            \Magento\Sales\Model\Order::STATE_PROCESSING => __('Processing'),
            \Magento\Sales\Model\Order::STATE_COMPLETE => __('Complete'),
            \Magento\Sales\Model\Order::STATE_HOLDED => __('Holded'),
            \Magento\Sales\Model\Order::STATE_CLOSED => __('Closed'),
            \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW => __('Payment Review'),
            \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT => __('Pending Payment'),
            \Magento\Sales\Model\Order::STATE_CANCELED => __('Canceled')
        ];
    }
}

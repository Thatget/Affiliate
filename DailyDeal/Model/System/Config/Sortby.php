<?php

namespace MW\DailyDeal\Model\System\Config;

class Sortby implements \Magento\Framework\Data\OptionSourceInterface
{
    const SORT_BY_FEATURED_ENDDATETIME = 1;
    const SORT_BY_RANDOM = 2;
    const SORT_BY_FEATURED_RANDOM = 3;

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
            self::SORT_BY_FEATURED_ENDDATETIME => __('Display Featured/Expiring Soonest Deals First')
            , self::SORT_BY_RANDOM => __('Display All Current Deals Randomly')
            , self::SORT_BY_FEATURED_RANDOM => __('Display Featured Deals Randomly')
        ];
    }
}

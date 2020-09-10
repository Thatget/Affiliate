<?php

namespace MW\DailyDeal\Model\System\Config;

class Sidebar implements \Magento\Framework\Data\OptionSourceInterface
{
    const LEFT_BAR = 1;
    const RIGHT_BAR = 2;
    const DISABLED = 0;

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
            self::LEFT_BAR => __('Sidebar')
//            , self::RIGHT_BAR => __('Right Bar')
            , self::DISABLED => __('Disabled')
        ];
    }
}

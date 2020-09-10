<?php

namespace MW\DailyDeal\Model\System\Config;

class Layout implements \Magento\Framework\Data\OptionSourceInterface
{
    const ONE_COLUMN = '1column';
    const TWO_COLUMN_RIGHT = '2columns-right';
    const TWO_COLUMN_LEFT = '2columns-left';
    const EMPTY_COLUMN = 'empty';
    const THREE_COLUMNS = '3columns';

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
            self::ONE_COLUMN => __('1 Column')
            , self::TWO_COLUMN_RIGHT => __('2 Columns Right Sidebar')
            , self::TWO_COLUMN_LEFT => __('2 Columns Left Sidebar')
            , self::THREE_COLUMNS => __('3 Columns')
            , self::EMPTY_COLUMN => __('Empty')
        ];
    }
}

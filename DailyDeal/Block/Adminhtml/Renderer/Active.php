<?php

namespace MW\DailyDeal\Block\Adminhtml\Renderer;

use MW\DailyDeal\Model\Status;

class Active extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $class = '';
        $text = '';
        $status = $row->getData('status');
        switch ($status) {
            case Status::STATUS_TIME_QUEUED:
                $class = 'grid-severity-notice';
                $text = __('Reindex required');
                break;
            case Status::STATUS_TIME_RUNNING:
                $class = 'grid-severity-major';
                $text = __('Ready');
                break;
            case Status::STATUS_TIME_DISABLED:
                $class = 'grid-severity-critical';
                $text = __('Processing');
                break;
            case Status::STATUS_TIME_ENDED:
                $class = 'grid-severity-critical';
                $text = __('Processing');
                break;
        }
        return '<span class="' . $class . '"><span>' . $text . '</span></span>';
    }
}

<?php

namespace MW\DailyDeal\Block\Adminhtml\Dealscheduler\Edit;

/**
 * Class Tabs
 * @package MW\DailyDeal\Block\Adminhtml\Dealitems\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('setting_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Setting'));
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->addTab(
            'setting_section',
            [
                'label'   => 'Setting Deal',
                'title'   => 'Setting Deal',
                'content' => $this->getChildBlock('deal_scheduler_edit_tab_deal')->toHtml(),
            ],
            'main_section'
        );

        $productBlock = $this->getChildBlock('deal_scheduler_edit_tab_product');

        $this->addTab('list_product', array(
            'label' => __('Select Product(s)'),
            'title' => __('Select Product(s)'),
            'url'       => $this->getUrl('*/*/dealproduct', array('_current' => true)),
            'class'     => 'ajax',
        ));

        return $this;
    }
}

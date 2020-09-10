<?php

namespace MW\DailyDeal\Block\Adminhtml\Dealitems\Edit;

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
        $this->setId('general_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Deal Information'));
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (!$this->getRequest()->getParam('dailydeal_id')) {
            $this->_activeTab = 'product_section';
            $productBlock = $this->getChildBlock('deal_edit_tab_product');
            $this->addTab(
                'product_section',
                [
                    'label'   => 'Product',
                    'title'   => 'Product',
                    'content' => $this->getChildHtml('deal_edit_tab_product').
                        $this->getChildBlock('deal_edit_tabs.serilaze')->setGridBlock($productBlock)->toHtml(),
                ],
                'product_section'
            );
        }

        $this->addTab(
            'main_section',
            [
                'label'   => 'Deal Information',
                'title'   => 'Deal Information',
                'content' => $this->getChildBlock('deal_edit_tab_deal')->toHtml(),
            ],
            'main_section'
        );

        $id = $this->getRequest()->getParam("dailydeal_id");
        if (isset($id)) {
            $this->addTab(
                'report',
                [
                    'label'   => 'Deal Report',
                    'title'   => 'Deal Report',
                    'content' => $this->getChildBlock('deal_edit_tab_deal_report')->toHtml(),
                ],
                'main_section'
            );
        }

        return $this;
    }
}

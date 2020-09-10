<?php

namespace MW\DailyDeal\Block\Adminhtml\Dealitems\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

class Report extends Generic implements TabInterface
{
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $objectFactory;

    /**
     * dailydeal factory.
     *
     * @var \MW\DailyDeal\Model\DailydealFactory
     */
    protected $dailydealFactory;

    /**
     * @var \MW\DailyDeal\Model\Dailydeal
     */
    protected $dailydeal;

    /**
     * stdlib timezone.
     *
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $stdTimezone;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $store;
    protected $coreRegistry;
    /**
     * constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\DataObjectFactory $objectFactory
     * @param \MW\DailyDeal\Model\Dailydeal $dailydeal
     * @param \MW\DailyDeal\Model\DailydealFactory $dailydealFactory
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param \Magento\Store\Model\System\Store $store
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\DataObjectFactory $objectFactory,
        \MW\DailyDeal\Model\Dailydeal $dailydeal,
        \MW\DailyDeal\Model\DailydealFactory $dailydealFactory,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        \Magento\Store\Model\System\Store $store,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->objectFactory = $objectFactory;
        $this->coreRegistry = $registry;
        $this->dailydeal = $dailydeal;
        $this->dailydealFactory = $dailydealFactory;
        $this->stdTimezone = $timezone;
        $this->store = $store;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();
        $this->setForm($form);
        $fieldset = $form->addFieldset('dailydeal_form', array(
            'legend' => __('Deal Report')
        ));

        $fieldset->addField('impression', 'label', array(
            'name' => 'impression',
            'title' => __('Number of Impressions'),
            'label' => __('Number of Impressions'),
        ));

        $fieldset->addField('sold_qty', 'label', array(
            'name' => 'sold_qty',
            'title' =>__('Sold Qty'),
            'label' =>__('Sold Qty'),
        ));

        $block = $this->getLayout()->createBlock(\MW\DailyDeal\Block\Adminhtml\Sales\Order\Grid::class);
        $this->setChild('form_after', $block);

        if ($this->coreRegistry->registry('deal_items')) {
            $id = $this->getRequest()->getParam("dailydeal_id");
            if (!empty($id)) {
                $form->setValues($this->setEditFormDefaultValue());
            }
        }

        return parent::_prepareForm();
    }

    protected function setEditFormDefaultValue()
    {
        $data = $this->coreRegistry->registry('deal_items');
        return $data;
    }

    protected function _prepareLayout()
    {
        $this->unsetChild('reset_filter_button');
        $this->unsetChild('search_button');
    }

    public function getMainButtonsHtml()
    {
        return '';
    }

    /**
     * Return Tab label
     *
     * @return string
     * @api
     */
    public function getTabLabel()
    {
        // TODO: Implement getTabLabel() method.
    }

    /**
     * Return Tab title
     *
     * @return string
     * @api
     */
    public function getTabTitle()
    {
        // TODO: Implement getTabTitle() method.
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @api
     */
    public function canShowTab()
    {
        // TODO: Implement canShowTab() method.
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     * @api
     */
    public function isHidden()
    {
        // TODO: Implement isHidden() method.
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        // TODO: Implement getTabClass() method.
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        // TODO: Implement getTabUrl() method.
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        // TODO: Implement isAjaxLoaded() method.
    }
}

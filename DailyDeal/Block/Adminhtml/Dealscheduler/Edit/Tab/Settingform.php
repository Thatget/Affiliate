<?php

namespace MW\DailyDeal\Block\Adminhtml\Dealscheduler\Edit\Tab;

use MW\DailyDeal\Model\Dailydeal;
use MW\DailyDeal\Model\Status;

class Settingform extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
        array $data = []
    ) {
        $this->objectFactory = $objectFactory;
        $this->coreRegistry = $registry;
        $this->dailydeal = $dailydeal;
        $this->dailydealFactory = $dailydealFactory;
        $this->stdTimezone = $timezone;
        $this->store = $store;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * prepare layout.
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('page.title')->setPageTitle($this->getPageTitle());

        \Magento\Framework\Data\Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock(
                'MW\DailyDeal\Block\Adminhtml\Form\Renderer\Fieldset\Element',
                $this->getNameInLayout().'_fieldset_element'
            )
        );

        return $this;
    }

    /**
     * Prepare form.
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $dealAttributes = $this->dailydeal->getStoreAttributes();
        $dealAttributesInStores = ['store_id' => ''];

        foreach ($dealAttributes as $dealAttribute) {
            $dealAttributesInStores[$dealAttribute.'_in_store'] = '';
        }

        $dataObj = $this->objectFactory->create(
            ['data' => $dealAttributesInStores]
        );
        $model = $this->coreRegistry->registry('current_deal_scheduler');

        if ($dealSchedulerId = $this->getRequest()->getParam('deal_scheduler_id')) {
            $model->setDealSchedulerId($dealSchedulerId);
        }
        if ($model != null) {
            $dataObj->addData($model->getData());
        }

        $storeViewId = $this->getRequest()->getParam('store');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix($this->dailydeal->getFormFieldHtmlIdPrefix());

        $fieldset = $form->addFieldset('dealscheduler_edit', array(
            'legend' =>__('Settings'),
        ));
        if ($model != null && $model->getId()) {
            $fieldset->addField('deal_scheduler_id', 'hidden', ['name' => 'deal_scheduler_id']);
        }

        $elements = [];

        $fieldset->addField('rule_auto_generate_deal', 'hidden', array(
            'name' => 'rule_auto_generate_deal',
            'value' => 0,
        ));

        $elements['title'] = $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Deal Generator Title '),
                'title' => __('Deal Generator Title '),
                'required' => true,
            ]
        );

        $elements['deal_price'] = $fieldset->addField(
            'deal_price',
            'text',
            [
                'name' => 'deal_price',
                'label' => __('Set Product Discount'),
                'title' => __('Set Product Discount'),
                'required' => true,
                'class' => 'validate-number',
            ]
        );

        $elements['deal_qty'] = $fieldset->addField(
            'deal_qty',
            'text',
            [
                'name' => 'deal_qty',
                'label' => __('Limit Quantity of Each Sale Item to:'),
                'title' => __('Limit Quantity of Each Sale Item to:'),
                'required' => false,
                'class' => 'validate-number',
            ]
        );

        $elements['number_deal'] = $fieldset->addField(
            'number_deal',
            'text',
            [
                'name' => 'number_deal',
                'label' => __('Set number of products to be on sale at same time'),
                'title' => __('Set number of products to be on sale at same time'),
                'required' => false,
                'class' => 'validate-number',
            ]
        );

        $elements['number_day'] = $fieldset->addField(
            'number_day',
            'text',
            [
                'name' => 'number_day',
                'label' => __('Set number of days in advance, deals are generated for'),
                'title' => __('Set number of days in advance, deals are generated for'),
                'required' => false,
                'class' => 'validate-number',
            ]
        );

        $elements['deal_time'] = $fieldset->addField(
            'deal_time',
            'text',
            [
                'name' => 'deal_time',
                'label' => __('Deal Duration Cycle (in hours) '),
                'title' => __('Deal Duration Cycle (in hours) '),
                'required' => false,
                'note'  => __('The active time for each deal before rotating to next deal')
            ]
        );

        $fieldMaps['generate_type'] = $fieldset->addField(
            'generate_type',
            'select',
            [
                'label' => __('Deal Generation Method'),
                'name' => 'generate_type',
                'disabled' => false,
                'values' => [
                    [
                        'value' => Status::DEAL_SCHEDULER_GENERATE_TYPE_RANDOMLY,
                        'label' => __('At Random'),
                    ],
                    [
                        'value' => Status::DEAL_SCHEDULER_GENERATE_TYPE_ROTATORS,
                        'label' => __('Sequentially'),
                    ],
                ],
                'note' => 'If you choose to have limited # of products in group on sale at one time'
            ]
        );

        $now = $this->stdTimezone->date()->format("Y-m-d H:i");
        $style = 'color: #000;background-color: #fff; font-weight: bold; font-size: 13px;';
        $elements['start_date_time'] = $fieldset->addField(
            'start_date_time',
            'date',
            [
                'name' => 'start_date_time',
                'label' => __('Starting time'),
                'title' => __('Starting time'),
                'required' => true,
                'readonly' => true,
                'style' => $style,
                'class' => 'required-entry',
                'date_format' => 'Y-M-d',
                'time_format' => 'H:m',
                'note' => __('Current time server is: %1', $now),
            ]
        );

        $elements['end_date_time'] = $fieldset->addField(
            'end_date_time',
            'date',
            array(
                'name' => 'end_date_time',
                'label' => __('Ending time'),
                'title' => __('Ending time'),
                'required' => true,
                'readonly' => true,
                'style' => $style,
                'class' => 'required-entry',
                'date_format' => 'Y-M-d',
                'time_format' => 'H:m',
                'note' => __('Current time server is: %1', $now)
            )
        );

        $elements['status'] = $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => false,
                'values' => [
                    [
                        'value' => Status::STATUS_FEATURED_ENABLED,
                        'label' => __('Enable'),
                    ],
                    [
                        'value' => Status::STATUS_FEATURED_DISABLED,
                        'label' => __('Disable'),
                    ],
                ],
                'note'  => __('Enable and Save Deal to activate')
            ]
        );

        $form->addValues($dataObj->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return mixed
     */
    public function getDealScheduler()
    {
        return $this->coreRegistry->registry('deal_scheduler');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getPageTitle()
    {
        return $this->getDealScheduler()->getId()
            ? __("Edit Deal Scheduler %1", $this->escapeHtml($this->getDealScheduler()
                ->getTitle())) : __('New Deal Scheduler');
    }

    /**
     * Prepare label for tab.
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Deal Scheduler');
    }

    /**
     * Prepare title for tab.
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Deal Scheduler');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}

<?php

namespace MW\DailyDeal\Block\Adminhtml\Dealitems\Edit\Tab;

use MW\DailyDeal\Model\Dailydeal;
use MW\DailyDeal\Model\Status;

class Mainform extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
        $model = $this->coreRegistry->registry('dealitems');

        if ($dealId = $this->getRequest()->getParam('dailydeal_id')) {
            $model->setDailydealId($dealId);
        }

        $dataObj->addData($model->getData());

        $storeViewId = $this->getRequest()->getParam('store');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix($this->dailydeal->getFormFieldHtmlIdPrefix());

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Deal Information')]);

        if ($model->getId()) {
            $fieldset->addField('deal_id', 'hidden', ['name' => 'dailydeal_id', 'value' => $model->getId()]);
        }
        $fieldset->addField('product_id', 'hidden', ['name' => 'product_id','id'=>'product_id']);
//        \Zend_Debug::dump($fieldset->getData());die();
        $elements = [];
        $elements['cur_product'] = $fieldset->addField(
            'cur_product',
            'text',
            [
                'name' => 'cur_product',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
                'readonly' => true,
                'note'   => '<a id="product_edit_link" target="_blank" href="'
                    .$this->getUrl('catalog/product/edit', array('id' => $dataObj->getProductId()))
                    .'">'.__('View product information.').'</a>
                    <!-- <a href="javascript:void(0);" 
                    onclick="document.getElementById(\'general_tabs_product_section\').click();">
                    '.__('Change Product.').'</a> -->'
            ]
        );

        $elements['product_price'] = $fieldset->addField(
            'product_price',
            'text',
            [
                'name' => 'product_price',
                'label' => __('Product Price'),
                'title' => __('Product Price'),
                'required' => true,
                'readonly' => true,
            ]
        );

//        $elements['product_qty'] = $fieldset->addField(
//            'product_qty',
//            'text',
//            [
//                'name' => 'product_qty',
//                'label' => __('Product Qty'),
//                'title' => __('Product Qty'),
//                'required' => true,
//                'readonly' => true,
//                'after_element_html' => '
//                    <br /><br />
//                    </td>
//                </tr>
//                <tr class="system-fieldset-sub-head">
//                    <td colspan="2">
//                    <h2 style="border-bottom: 1px solid #CCCCCC; width : 100%">'.__('Deal Setting') . '</h2>',
//            ]
//        );
//
        $elements['product_sku'] = $fieldset->addField(
            'product_sku',
            'text',
            [
                'name' => 'product_sku',
                'label' => __('SKU'),
                'title' => __('SKU'),
                'required' => true,
                'readonly' => true,
            ]
        );
        $fieldset = $form->addFieldset('setting_field', ['legend' => __('Deal Setting')]);

        $elements['discount'] = $fieldset->addField(
            'discount',
            'text',
            [
                'name' => 'discount',
                'label' => __('% Discount'),
                'title' => __('% Discount'),
                'required' => true,
                'class' => 'validate-number'
            ]
        );

        $elements['dailydeal_price'] = $fieldset->addField(
            'dailydeal_price',
            'text',
            [
                'name' => 'dailydeal_price',
                'label' => __('Deal Price'),
                'title' => __('Deal Price'),
                'required' => true,
                'class' => 'validate-number',
                'note'  => __('Please enter a number greater than 0 and smaller \'Product\'s Price\' in this field.')
            ]
        );

        $elements['deal_qty'] = $fieldset->addField(
            'deal_qty',
            'text',
            [
                'name' => 'deal_qty',
                'label' => __('Deal Qty'),
                'title' => __('Deal Qty'),
                'required' => false,
                'class' => 'validate-number',
                'note' => __('Leave blank for no limit.'),
            ]
        );

        $fieldset->addField('store_view', 'multiselect', array(
            'name' => 'store_view[]',
            'label' => __('Store View'),
            'title' => __('Store View'),
            'required' => true,
            'values' => $this->store->getStoreValuesForForm(),
        ));

        $elements['limit_customer'] = $fieldset->addField(
            'limit_customer',
            'text',
            [
                'name' => 'limit_customer',
                'label' => __('Limit deals per customer order'),
                'title' => __('Limit deals per customer order'),
                'required' => false,
                'class' => 'validate-number',
                'note' => __('Leave empty or 0 for unlimited deal qty per order.'),
            ]
        );

        $fieldMaps['disable_product_after_finish'] = $fieldset->addField(
            'disable_product_after_finish',
            'select',
            [
                'label' => __('Disable product after finish'),
                'name' => 'disable_product_after_finish',
                'disabled' => false,
                'default' => Status::STATUS_DISABLED,
                'values' => [
                    [
                        'value' => Status::STATUS_DISABLED,
                        'label' => __('No'),
                    ],
                    [
                        'value' => Status::STATUS_ENABLED,
                        'label' => __('Yes'),
                    ],
                ],
                'note' => 'Default:No'
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

        $fieldMaps['featured'] = $fieldset->addField(
            'featured',
            'select',
            [
                'label' => __('Featured Deal'),
                'name' => 'featured',
                'values' => [
                    [
                        'value' => Status::STATUS_FEATURED_ENABLED,
                        'label' => __('Yes'),
                    ],
                    [
                        'value' => Status::STATUS_FEATURED_DISABLED,
                        'label' => __('No'),
                    ],
                ],
                'note'  => __('Featured Deals appear first/highest in deal blocks')
            ]
        );

        $fieldMaps['status'] = $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'name' => 'status_deal',
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

        $elements['description'] = $fieldset->addField(
            'description',
            'textarea',
            [
                'name' => 'description',
                'label' => __('Deal Description'),
                'title' => __('Deal Description'),
                'required' => false,
            ]
        );

        $form->addValues($dataObj->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return mixed
     */
    public function getDeal()
    {
        return $this->coreRegistry->registry('deal_items');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getPageTitle()
    {
        if ($this->getRequest()->getParam('dailydeal_id')) {
            return $this->getDeal()->getId()
                ? __("Edit Deal '%1'", $this->escapeHtml($this->getDeal()->getCurProduct())) : __('New Deal');
        }
        return __('New Deal');
    }

    /**
     * Prepare label for tab.
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Deal Information');
    }

    /**
     * Prepare title for tab.
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Deal Information');
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

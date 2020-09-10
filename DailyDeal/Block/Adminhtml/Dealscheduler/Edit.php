<?php

namespace MW\DailyDeal\Block\Adminhtml\Dealscheduler;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry           $registry
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     */
    protected function _construct()
    {
        $this->_objectId = 'deal_scheduler_id';
        $this->_blockGroup = 'MW_DailyDeal';
        $this->_controller = 'adminhtml_dealscheduler';
        $this->buttonList->remove('reset');

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Deal'));
        $this->buttonList->update('delete', 'label', __('Delete'));
        $this->buttonList->remove('reset');

        $this->buttonList->add(
            'save_apply',
            [
                'label' => __('Save & Start Deal Generator'),
                'class' => 'save',
                'onclick' => "jQuery('#page_rule_auto_generate_deal')[0].value=1;",
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ],
            ],
            -100
        );
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ],
            ],
            -100
        );

        $this->_formScripts[] = '   
            function toggleEditor() {
                if (tinyMCE.getInstanceById(\'dealitem_content\') == null) {
                    tinyMCE.execCommand(\'mceAddControl\', false, \'dealitem_content\');
                } else {
                    tinyMCE.execCommand(\'mceRemoveControl\', false, \'dealitem_content\');
                }
            }
       
            require([
                    "jquery",
                    "underscore",
                    "mage/mage",
                    "mage/backend/tabs",
                    "domReady!"
                ], function($) {
                    var $form = $(\'#edit_form\');
                    $form.mage(\'form\', {
                        handlersData: {
                            save: {},
                            saveAndNew: {
                                action: {
                                    args: {back: \'new\'}
                                }
                            },
                        }
                    });
                    jQuery(\'#page_rule_auto_generate_deal\')[0].value=0;
                });
        ';
    }
}

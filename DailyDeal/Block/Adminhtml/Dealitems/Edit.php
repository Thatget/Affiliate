<?php

namespace MW\DailyDeal\Block\Adminhtml\Dealitems;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
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
        $this->_objectId = 'dailydeal_id';
        $this->_blockGroup = 'MW_DailyDeal';
        $this->_controller = 'adminhtml_dealitems';

        parent::_construct();

        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');

        $this->buttonList->add(
            'back',
            [
                'label' => __('Back'),
                'class' => 'back',
                'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/indexFilterAll') . '\')',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'back', 'target' => '#edit_form']],
                ],
            ],
            -100
        );

        $this->buttonList->update('delete', 'label', __('Delete'));
        $this->buttonList->update('save', 'label', __('Save Deal'));

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
//        $this->buttonList->add(
//            'new-button',
//            [
//                'label' => __('Save and New'),
//                'class' => 'save',
//                'data_attribute' => [
//                    'mage-init' => [
//                        'button' => ['event' => 'saveAndNew', 'target' => '#edit_form'],
//                    ],
//                ],
//            ],
//            10
//        );

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

                        });
                    
        
        ';
    }
}

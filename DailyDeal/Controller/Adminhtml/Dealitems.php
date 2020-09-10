<?php

namespace MW\DailyDeal\Controller\Adminhtml;

use MW\DailyDeal\Controller\Adminhtml\AbstractAction;

abstract class Dealitems extends AbstractAction
{
    /**
     * param id for crud action : edit,delete,save.
     */
    const PARAM_CRUD_ID = 'dailydeal_id';

    /**
     * registry name.
     */
    const REGISTRY_NAME = 'dealitems';

    /**
     * Init page.
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('MW_DailyDeal::mw_dailydeal_parent')
            ->addBreadcrumb(__('Daily Deal'), __('Daily Deal'))
            ->addBreadcrumb(__('Manage Deal'), __('Manage Current Deal Running'));

        return $resultPage;
    }

    /**
     * Check the permission to run it.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_DailyDeal::dealitems_indexdeals');
    }

    /**
     * Get back result redirect after add/edit.
     *
     * @param \Magento\Framework\Controller\Result\Redirect $resultRedirect
     * @param null                                          $paramCrudId
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
//    protected function _getBackResultRedirect(\Magento\Framework\Controller\Result\Redirect $resultRedirect, $paramCrudId = null)
//    {
//        switch ($this->getRequest()->getParam('back')) {
//            case 'edit':
//                $resultRedirect->setPath(
//                    '*/*/edit',
//                    [
//                        static::PARAM_CRUD_ID => $paramCrudId,
//                        '_current' => true,
//                        'store' => $this->getRequest()->getParam('store'),
//                        'current_deal_id' => $this->getRequest()->getParam('id'),
//                        'saveandclose' => $this->getRequest()->getParam('saveandclose'),
//                    ]
//                );
//                break;
//            case 'new':
//                $resultRedirect->setPath('*/*/new', ['_current' => true]);
//                break;
//            default:
//                $resultRedirect->setPath('*/*/');
//        }
//
//        return $resultRedirect;
//    }
}

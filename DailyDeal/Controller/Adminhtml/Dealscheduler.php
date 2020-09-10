<?php

namespace MW\DailyDeal\Controller\Adminhtml;

abstract class Dealscheduler extends \MW\DailyDeal\Controller\Adminhtml\AbstractAction
{
    /**
     * param id for crud action : edit,delete,save.
     */
    const PARAM_CRUD_ID = 'deal_scheduler_id';

    /**
     * registry name.
     */
    const REGISTRY_NAME = 'deal_scheduler';

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
            ->addBreadcrumb(__('Deal Scheduler'), __('Deal Scheduler'))
            ->addBreadcrumb(__('Manage Deal Scheduler'), __('Manage Deal Scheduler'));

        return $resultPage;
    }

    /**
     * Check the permission to run it.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_DailyDeal::dealscheduler');
    }
}

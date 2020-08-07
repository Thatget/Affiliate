<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatereport;

class Referral extends \MW\Affiliate\Controller\Adminhtml\Affiliatereport
{
    /**
     * Affiliate invitation report page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_Affiliate::affiliate');
        $resultPage->getConfig()->getTitle()->prepend(__('Affiliate Invitation Statistic'));

        return $resultPage;
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_Affiliate::referral');
    }
}

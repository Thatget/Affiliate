<?php

namespace MW\DailyDeal\Controller\Adminhtml\Dealitems;

use Magento\Framework\Controller\ResultFactory;

class Index extends \MW\DailyDeal\Controller\Adminhtml\Dealitems
{
    /**
     * Index action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $this->initPage($resultPage);
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Deal'));

        return $resultPage;
    }
}

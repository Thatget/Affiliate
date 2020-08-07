<?php

namespace MW\Affiliate\Controller\Index;

class Createaccount extends \MW\Affiliate\Controller\Createaccount
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Signup Affiliate Account Information'));
        return $resultPage;
    }
}

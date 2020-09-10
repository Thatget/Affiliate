<?php

namespace MW\DailyDeal\Controller\Adminhtml\Dealitems;

use Magento\Framework\Controller\ResultFactory;

class NewAction extends \MW\DailyDeal\Controller\Adminhtml\Dealitems
{
    /**
     * Create new Dealitems.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);

        return $resultForward->forward('edit');
    }
}

<?php

namespace MW\DailyDeal\Controller\Adminhtml\Dealitems;

/**
 * Class Save
 * @package MW\DailyDeal\Controller\Adminhtml\Dealscheduler
 */

class MassDelete extends \MW\DailyDeal\Controller\Adminhtml\Dealitems
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $collection = $this->_massActionFilter->getCollection($this->_createMainCollection());
        $collectionSize = $collection->getSize();

        foreach ($collection as $item) {
            $item->delete();
        }

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*/indexFilterAll');
    }
}

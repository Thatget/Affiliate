<?php

namespace MW\DailyDeal\Controller\Adminhtml\Dealitems;

/**
 * Class Save
 * @package MW\DailyDeal\Controller\Adminhtml\Dealscheduler
 */

class Delete extends \MW\DailyDeal\Controller\Adminhtml\Dealitems
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam(static::PARAM_CRUD_ID);
        if ($id) {
            try {
                /** @var \MW\DailyDeal\Model\Dealscheduler $model */
                $model = $this->_createMainModel();
                $model->setId($id)->delete();
                $this->messageManager->addSuccess(__('You deleted the Deal Scheduler.'));

                return $resultRedirect->setPath('*/*/indexFilterAll');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $resultRedirect->setPath('*/*/indexFilterAll', [static::PARAM_CRUD_ID => $id]);
            }
        }
        $this->messageManager->addError(__('We can\'t find a item to delete.'));

        return $resultRedirect->setPath('*/*/indexFilterAll');
    }
}

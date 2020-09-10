<?php

namespace MW\DailyDeal\Controller\Adminhtml\Dealitems;

use Magento\Framework\Controller\ResultFactory;

class Edit extends \MW\DailyDeal\Controller\Adminhtml\Dealitems
{
    /**
     * Edit Deal Items.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam(static::PARAM_CRUD_ID);
        $storeViewId = $this->getRequest()->getParam('store');
        /** @var \MW\DailyDeal\Model\Dailydeal $model */
        $model = $this->_createMainModel();
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This Dealitems no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/sss');
            }

            $this->_coreRegistry->register('deal_product_id', $model->getProductId());
            $this->_coreRegistry->register('deal_items', $model);
        }

        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register(static::REGISTRY_NAME, $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Deal Items') : __('New Deal Items'),
            $id ? __('Edit Deal Items') : __('New Deal Items')
        );

        $resultPage->getConfig()->getTitle()->prepend(__('Manage Deal Items'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ?
            __('Edit Deal Items %1', $this->_escaper->escapeHtml($model->getCurProduct())) : __('New Deal Items')
        );

        return $resultPage;
    }
}

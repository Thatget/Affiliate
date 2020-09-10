<?php

namespace MW\DailyDeal\Controller\Adminhtml\Dealscheduler;

/**
 * Class Save
 * @package MW\DailyDeal\Controller\Adminhtml\Dealscheduler
 */

class Save extends \MW\DailyDeal\Controller\Adminhtml\Dealscheduler
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPostValue()) {
            $model = $this->dealSchedulerFactory();
//            \Zend_Debug::dump($data); die;
            if ($id = $this->getRequest()->getParam(static::PARAM_CRUD_ID)) {
                $model->load($id);
                $model->setData('deal_scheduler_id', $id);
            }

            if (!isset($data['start_date_time'])) {
                $data['start_date_time'] = $this->date->date()->format('Y-m-d H:i:s');
            }
            if (!isset($data['end_date_time'])) {
                $data['end_date_time'] = $this->date->date()->format('Y-m-d H:i:s');
            }
            $localeDate = $this->localeDate;
            $data['start_date_time'] = date('Y-m-d H:i:s', strtotime($data['start_date_time']));
            $data['end_date_time'] = date('Y-m-d H:i:s', strtotime($data['end_date_time']));

            if (!isset($data['deal_price'])||$data['deal_price']<0) {
                $data['deal_price']=0;
            }
            if (!isset($data['deal_qty']) || $data['deal_qty']<0) {
                $data['deal_qty']=0;
            }
            $dealData = [];
            $dealData['title'] = $data['title'];
            $dealData['deal_time'] = $data['deal_time'];
            $dealData['deal_price'] = $data['deal_price'];
            if ($dealData['deal_price'] > 100) {
                $this->messageManager->addError(__('Discount invalid!'));
                return $this->_getBackResultRedirect($resultRedirect, $model->getId());
            }
            $dealData['deal_qty'] = $data['deal_qty'];
            $dealData['number_deal'] = $data['number_deal'];
            $dealData['number_day'] = $data['number_day'];
            $dealData['generate_type'] = $data['generate_type'];
            $dealData['start_date_time'] = $data['start_date_time'];
            $dealData['end_date_time'] = $data['end_date_time'];
            $dealData['status'] = $data['status'];
            $model->setData($dealData)
                ->setId($id);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('The Deal Scheduler has been saved.'));
                $this->_getSession()->setFormData(false);
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addException($e, __('Something went wrong while saving.'));
            }

            if (!empty($data['product_ids'])) {
                // User has choice product
                /** @var $product_scheduler \MW\DailyDeal\Model\Dealschedulerproduct */
                $product_scheduler = $this->dealSchedulerProduct;
                $product_scheduler->deleteAllByDealSchedulerId($model->getId());
                $proArray = explode("&", $data['product_ids']);
                $dealArray = [];
                foreach ($proArray as $pro) {
                    $arrayData = explode('=', $pro);
                    $productId = $arrayData[0];
                    $position = $arrayData[1];
                    $dealArray[$productId]['deal_scheduler_id'] = $model->getId();
                    $dealArray[$productId]['deal_time'] = $dealData['deal_time'];
                    $dealArray[$productId]['deal_price'] = $dealData['deal_price'];
                    $deal_qty_data = str_replace('%3D', '', explode("=", $pro)[1]);
                    $deal_qty_data_decode = base64_decode($deal_qty_data);
                    $deal_qty = explode("=", $deal_qty_data_decode)[1];
                    $dealArray[$productId]['product_deal_qty'] = $deal_qty;
                    $dealArray[$productId]['position'] = 0;
                }
                foreach ($dealArray as $key => $dealProduct) {
                    $product_id = $key;
                    $product_scheduler->setData([]);
                    $product_scheduler->setData('deal_scheduler_id', $dealProduct['deal_scheduler_id']);
                    $product_scheduler->setData('product_id', $product_id);
                    $product_scheduler->setData('deal_time', $dealProduct['deal_time']);
                    $product_scheduler->setData('deal_price', $dealProduct['deal_price']);

                    $product_scheduler->setData('product_deal_qty', $dealProduct['product_deal_qty']);
                    $product_scheduler->setData('deal_position', $dealProduct['position']);
                    try {
                        $product_scheduler->setId(null)->save();
                        $this->messageManager->addSuccess(__('The Deal Scheduler Product has been saved.'));
                    } catch (\Exception $e) {
                        $this->messageManager->addError($e->getMessage());
                        $this->messageManager->addException($e, __('Something went wrong while saving.'));
                    }
                }
            }

            $this->_getSession()->setFormData($data);

            if (!empty($data['rule_auto_generate_deal'])) {
                $count = $this->business->generalDeal($model->getId());

                $this->messageManager->addSuccess(__('Generate %1 deals successful!', $count));
                $this->_redirect('*/dealitems/indexFilterAll/');
            }
            return $this->_getBackResultRedirect($resultRedirect, $model->getId());
        }

        return $resultRedirect->setPath('*/*/');
    }
}

<?php

namespace MW\DailyDeal\Controller\Adminhtml\Dealitems;
/**
 * Class Save
 * @package MW\DailyDeal\Controller\Adminhtml\Dealitems
 */

class Save extends \MW\DailyDeal\Controller\Adminhtml\Dealitems
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPostValue()) {
            $model = $this->dailydealFactory->create();
            $storeView = $this->getRequest()->getParam('store_view');
            $storeViewId = implode(",", $storeView);
            $id = $this->getRequest()->getParam(static::PARAM_CRUD_ID);
            if ($id) {
                $model->load($id);
            }
            if (!isset($data['start_date_time'])) {
                $data['start_date_time'] = $this->date->date()->format('Y-m-d H:i:s');
            }
            if (!isset($data['end_date_time'])) {
                $data['end_date_time'] = $this->date->date()->format('Y-m-d H:i:s');
            }
            if (strtotime($data['start_date_time']) > strtotime($data['start_date_time'])) {
                $this->messageManager->addError(__('Something went wrong while saving the deal.'));
                return $this->_getBackResultRedirect($resultRedirect, $model->getId());
            }

            if (!isset($data['limit_customer']) || $data['limit_customer']<0) {
                $data['limit_customer']=0;
            }
            if (!isset($data['deal_qty']) || $data['deal_qty']<0) {
                $data['deal_qty']=0;
            }
            $dealData = [];
            $dealData['product_id'] = $data['product_id'];
            $dealData['cur_product'] = $data['cur_product'];
            $dealData['product_sku'] = $data['product_sku'];
            $dealData['product_price'] = $data['product_price'];
            $dealData['discount'] = $data['discount'];
            if ($dealData['discount'] > 100) {
                $this->messageManager->addError(__('% Discount invalid!'));
                return $resultRedirect->setPath(
                    '*/*/edit/',
                    [static::PARAM_CRUD_ID => $this->getRequest()->getParam(static::PARAM_CRUD_ID)]
                );
            }
            $dealData['start_date_time'] = date('Y-m-d H:i:s', strtotime($data['start_date_time']));
            $dealData['end_date_time'] = date('Y-m-d H:i:s', strtotime($data['end_date_time']));
            $dealData['dailydeal_price'] = $data['dailydeal_price'];
            $dealData['deal_qty'] = $data['deal_qty'];
            $dealData['description'] = $data['description'];
            $dealData['featured'] = $data['featured'];
            $dealData['disable_product_after_finish'] = $data['disable_product_after_finish'];
            $dealData['active'] = 0;
            $dealData['store_view'] = $storeViewId;
            $dealData['limit_customer'] = $data['limit_customer'];
            $dealData['status'] = $this->getStatusByDay(
                $dealData['start_date_time'],
                $dealData['end_date_time'],
                $data['status_deal']
            );
            if (strtotime($dealData['end_date_time']) < strtotime($dealData['start_date_time'])) {
                $this->messageManager->addError(__('End Time invalid!'));
                return $resultRedirect->setPath(
                    '*/*/edit/',
                    [static::PARAM_CRUD_ID => $this->getRequest()->getParam(static::PARAM_CRUD_ID)]
                );
            }
            $model->setData($dealData)
                ->setId($id);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('The Deal has been saved.'));
                $this->_getSession()->setFormData(false);
                return $this->_getBackResultRedirect($resultRedirect, $model->getId());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addException($e, __('Something went wrong while saving the deal.'));
            }

            $this->_getSession()->setFormData($data);

            return $resultRedirect->setPath(
                '*/*/edit/',
                [static::PARAM_CRUD_ID => $this->getRequest()->getParam(static::PARAM_CRUD_ID)]
            );
        }

        return $resultRedirect->setPath('*/*/');
    }

    public function getStatusByDay($startTime, $endTime, $status)
    {
        $timeStamp = $this->stdTimezone->date()->format("Y-m-d H:i:s");
        $now = strtotime($timeStamp);
        $start = strtotime($startTime);
        $end = strtotime($endTime);
        if ($status == 2) {
            return 2;
        }
        if ($start >= $end) {
            return 5;
        }
        if ($start > $now) {
            return 0;
        }
        if (($start <= $now) && ($end >= $now)) {
            return 1;
        }
        if ($end < $now) {
            return 3;
        }
    }
}

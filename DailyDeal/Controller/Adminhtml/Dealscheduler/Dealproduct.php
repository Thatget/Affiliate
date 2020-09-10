<?php

namespace MW\DailyDeal\Controller\Adminhtml\Dealscheduler;

use Magento\Framework\Controller\ResultFactory;

class Dealproduct extends \MW\DailyDeal\Controller\Adminhtml\Dealscheduler
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('deal_scheduler_id');
        $products = $this->dealSchedulerProduct->getProductOptionArray($id);
        $this->_coreRegistry->register('products', $products);

        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}

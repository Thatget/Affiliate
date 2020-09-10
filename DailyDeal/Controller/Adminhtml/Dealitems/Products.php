<?php

namespace MW\DailyDeal\Controller\Adminhtml\Dealitems;

use Magento\Framework\Controller\ResultFactory;

class Products extends \MW\DailyDeal\Controller\Adminhtml\Dealitems
{
    /**
     * Execute action
     */
    public function execute()
    {
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $resultLayout->getLayout()->getBlock('deal.deal.edit.tab.products');

        return $resultLayout;
    }
}

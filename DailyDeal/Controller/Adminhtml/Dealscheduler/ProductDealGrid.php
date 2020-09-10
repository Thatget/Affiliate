<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MW\DailyDeal\Controller\Adminhtml\Dealscheduler;

class ProductDealGrid extends \MW\DailyDeal\Controller\Adminhtml\Dealscheduler
{
    /**
     * Get upsell products grid
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->productBuilder->build($this->getRequest());
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('dailydeal.dealscheduler.edit.product.grid');
        return $resultLayout;
    }
}

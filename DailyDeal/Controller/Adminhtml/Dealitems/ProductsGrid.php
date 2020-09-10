<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MW\DailyDeal\Controller\Adminhtml\Dealitems;

class ProductsGrid extends \MW\DailyDeal\Controller\Adminhtml\Dealitems
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
        $resultLayout->getLayout()->getBlock('deal.deal.edit.tab.products');
        return $resultLayout;
    }
}

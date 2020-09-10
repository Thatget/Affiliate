<?php

namespace MW\DailyDeal\Controller\Adminhtml\Dealitems;

use MW\DailyDeal\Model\Status;

class ChangeProduct extends \MW\DailyDeal\Controller\Adminhtml\Dealitems
{
    /**
     *
     * @return
     */
    public function execute()
    {
        $product_id = $this->getRequest()->getParam('product_id');
        $array = [];
        if ($product_id) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $product = $objectManager->get('Magento\Catalog\Model\Product')->load($product_id);
            $product = $product->load($product_id);
            $array['id'] = $product->getId();
            $array['name'] = $product->getName();
            if ($product->getTypeId() == Status::TYPE_CONFIGURABLE) {
                $array['price'] = $product->getData('price');
            } else {
                $array['price'] = $product->getPrice();
            }
            $array['sku'] = $product->getSku();
            $qtyAndStockStatus = $product->getQuantityAndStockStatus();
            $array['qty'] = $qtyAndStockStatus['qty'];
            $array['url'] = $this->getUrl('catalog/product/edit', array('id' => $product_id));
        }
        $result = $this->getJsonFactory()->create();
        $result->setData($array);
        return $result;
    }
}

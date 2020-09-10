<?php
namespace MW\DailyDeal\Plugin;

use Magento\Framework\Pricing\SaleableInterface;

class SpecialPrice
{
    protected $helperConfig;
    protected $dailydealModel;
    protected $registry;
    protected $configurable;

    /**
     * Initialize dependencies.
     *
     * @param \MW\DailyDeal\Helper\Config $helperConfig
     * @param \MW\DailyDeal\Model\Dailydeal $dailydealModel
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \MW\DailyDeal\Helper\Config $helperConfig,
        \MW\DailyDeal\Model\Dailydeal $dailydealModel,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable,
        \Magento\Framework\Registry $registry
    ) {
        $this->helperConfig = $helperConfig;
        $this->dailydealModel = $dailydealModel;
        $this->configurable = $configurable;
        $this->registry = $registry;
    }

    /**
     * Retrieve all allowed countries or specific by scope depends on customer share setting
     *
     * @param \Magento\Catalog\Pricing\Price\SpecialPrice $subject
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSpecialPrice(\Magento\Catalog\Pricing\Price\SpecialPrice $subject)
    {
        $product = $subject->getProduct();
        $curProduct = $this->getCurrentProduct();
        if ($curProduct) {
            $id = $curProduct->getId();
        } else {
            $id = $product->getId();
            $product = $product = $this->configurable
                ->getParentIdsByChild($id);
            if (isset($product[0])) {
                //this is parent product id..
                $id = $product[0];
            }
        }
        $deal = $this->dailydealModel->loadActiveDealByProductId($id);
        if ($deal) {
            $discount = $deal->getDiscount();
            $specialPrice = $subject->getProduct()->getPrice() * (100 - $discount) / 100;
            return $specialPrice;
        }
    }

    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }
}

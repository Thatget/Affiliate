<?php

namespace MW\RewardPoints\Model\Plugin\Product\Type;

class Configurable
{

    public function aroundAssignProductToOption(
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $subject,callable $proceed,$optionProduct, $option, $product
    ){
        if ($optionProduct) {
            $option->setProduct($optionProduct);
        } else {
            $value = json_decode($option->getData('value'),true);
            if(array_first($value)['code'] = "sell_in_point") return $this;
            $option->getItem()->setHasConfigurationUnavailableError(true);
        }
        return $this;
    }
}

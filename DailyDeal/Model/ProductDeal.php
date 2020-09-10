<?php

namespace MW\DailyDeal\Model;

class ProductDeal extends \Magento\Framework\Model\AbstractModel
{
    protected $dailyDealModel;
    protected $productRepository;

    /**
     * @param \MW\DailyDeal\Model\Dailydeal $dailyDealModel
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \MW\DailyDeal\Model\Dailydeal $dailyDealModel,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->dailyDealModel = $dailyDealModel;
        $this->productRepository = $productRepository;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * If deal is expire and disable_product equal 1 -> disable product
     * @param array $deal_ids
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function disableProductByDealId($deal_ids = [])
    {
        $model_deal = $this->dailyDealModel;

        foreach ($deal_ids as $id) {
            $model_deal->setData([]);
            $model_deal->load($id);

            if ($model_deal->getId()) {
                if ($model_deal->getStatusTime() == Status::STATUS_TIME_ENDED) {
                    if ($model_deal->getData('disable_product_after_finish') == Status::STATUS_PRODUCT_ENABLED) {
                        $product = $this->productRepository->getById($model_deal->getProductId());
                        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                        $this->productRepository->save($product);
                        $model_deal->setData('disable_product_after_finish', Status::STATUS_PRODUCT_DONE);
                        $model_deal->save();
                    }
                }
            }
        }
    }

    /**
     * If deal is expire and disable_product equal 1 -> disable product
     * @param array $deal_ids
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function enableProductByDealId($deal_ids = [])
    {
        $model_deal = $this->dailyDealModel;

        foreach ($deal_ids as $id) {
            $model_deal->setData([]);
            $model_deal->load($id);

            if ($model_deal->getId()) {
                if ($model_deal->getData('disable_product_after_finish') == Status::STATUS_PRODUCT_DONE) {
                    $product = $this->productRepository->getById($model_deal->getProductId());
                    $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                    $this->productRepository->save($product);
                    $model_deal->setData('disable_product_after_finish', Status::STATUS_PRODUCT_ENABLED);
                    $model_deal->save();
                }
            }
        }
    }

    public static function getMinPriceProductConfig($product)
    {
        $regularPrice = $product->getPriceInfo()->getPrice('regular_price');
        $min_price = $regularPrice->getMinRegularAmount();
        return $min_price->getValue();
    }

    public function getMinPriceProductGrouped($product)
    {
        if ($product->getTypeId() != \Magento\GroupedProduct\Ui\DataProvider\Product\Form\Modifier\CustomOptions::PRODUCT_TYPE_GROUPED) {
            return '';
        }
        $aProductIds = $product->getTypeInstance()->getChildrenIds($product->getId());
        $prices = [];

        foreach ($aProductIds as $ids) {
            foreach ($ids as $id) {
                $aProduct = $this->productRepository->getById($id);
                $prices[] = $aProduct->getPriceModel()->getPrice($aProduct);
            }
        }

        sort($prices);
        $min_price = array_shift($prices);
        return $min_price;
    }

    public function getMinPriceProductBundle($product)
    {
        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            return '';
        }
        $bundleObj = $product->getPriceInfo()->getPrice('final_price');
        return $bundleObj->getMinimalPrice();
    }
}

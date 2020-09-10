<?php

namespace MW\DailyDeal\Model;

class Order extends \Magento\Framework\Model\AbstractModel
{
    protected $dailyDealModel;
    protected $productDealModel;
    protected $productRepository;
    protected $serializer;

    /**
     * @param \MW\DailyDeal\Model\Dailydeal $dailyDealModel
     * @param \MW\DailyDeal\Model\ProductDeal $productDealModel
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \MW\DailyDeal\Model\Dailydeal $dailyDealModel,
        \MW\DailyDeal\Model\ProductDeal $productDealModel,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->dailyDealModel = $dailyDealModel;
        $this->productDealModel = $productDealModel;
        $this->productRepository = $productRepository;
        $this->serializer = $serializer;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Front end when place order : mark order's product_id follow deal_id
     */
    public function markProductOfDeal($item, $deal)
    {
        $data = $item->getProductOptions();
        $data['info_buyRequest']['dailydeal_id'] = $deal->getData('dailydeal_id');
        return $data;
    }

    /**
     * Backend :when order update to 'cancel' -> deal's qty is update
     * @param $observer
     * @throws \Exception
     */
    public function cancelOrderUpdateDealQuantity($observer)
    {
        $model_deal = $this->dailyDealModel;

        $order = $observer->getData('order');
        $items = $order->getAllVisibleItems();

        foreach ($items as $item) {
            $product_option = $this->serializer->unserialize($item->getData('product_options'));
            $info_buyRequest = $product_option['info_buyRequest'];
            $dailydeal_id = $info_buyRequest['dailydeal_id'];

            $model_deal->setData(array());
            $model_deal->load($dailydeal_id);

            if ($model_deal->getId() && $model_deal->getData('product_id') == $item->getData('product_id')) {
                $order_sold_qty = $item['qty_ordered'];
                $deal_sold_qty = $model_deal->getData('sold_qty');
                $model_deal->setData('sold_qty', $deal_sold_qty - $order_sold_qty);
                $model_deal->save();
            }
        }
    }

    /**
     * Backend : when order update to 'cancel' -> product is enable
     * @param  $observer
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function cancelOrderEnableProduct($observer)
    {
        $model_deal = $this->dailyDealModel;

        $order = $observer->getData('order');
        $items = $order->getAllVisibleItems();

        foreach ($items as $item) {
            $product_option = $this->serializer->unserialize($item->getData('product_options'));
            $info_buyRequest = $product_option['info_buyRequest'];
            $dailydeal_id = $info_buyRequest['dailydeal_id'];

            $model_deal->setData([]);
            $model_deal->load($dailydeal_id);

            if ($model_deal->getId() && $model_deal->getData('product_id') == $item->getData('product_id')) {
                $this->productDealModel->enableProductByDealId(array($model_deal->getId()));
            }
        }
    }
}

<?php
namespace MW\RewardPoints\Model\ResourceModel\Quote\Item;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class Collection extends \Magento\Quote\Model\ResourceModel\Quote\Item\Collection {

    /**
     * @var bool $recollectQuote
     */
    private $recollectQuote = false;
    /**
     * Check is valid product.
     *
     * @param ProductInterface $product
     * @return bool
     */
    private function isValidProduct(?ProductInterface $product): bool
    {
        $result = ($product && (int)$product->getStatus() !== ProductStatus::STATUS_DISABLED);

        return $result;
    }
    /**
     * Get product Ids from option.
     *
     * @param QuoteItem $item
     * @param ProductInterface $product
     * @param ProductCollection $productCollection
     * @return array
     */
    private function getOptionProductIds(
        QuoteItem $item,
        ProductInterface $product,
        ProductCollection $productCollection
    ): array {
        $optionProductIds = [];
        foreach ($item->getOptions() as $option) {
            if (!$productCollection->getItemById($option->getProductId())) {
                $value = json_decode($option->getData('value'));
                if(array_first($value)->code = 'sell_in_point') continue;
            }
            /**
             * Call type-specific logic for product associated with quote item
             */
            $product->getTypeInstance()->assignProductToOption(
                $productCollection->getItemById($option->getProductId()),
                $option,
                $product
            );

            if (is_object($option->getProduct()) && $option->getProduct()->getId() != $product->getId()) {
                $isValidProduct = $this->isValidProduct($option->getProduct());
                if (!$isValidProduct && !$item->isDeleted()) {
                    $item->isDeleted(true);
                    $this->recollectQuote = true;
                    continue;
                }
                $optionProductIds[$option->getProduct()->getId()] = $option->getProduct()->getId();
            }
        }

        return $optionProductIds;
    }
    /**
     * Add products to items and item options.
     *
     * @return \Magento\Quote\Model\ResourceModel\Quote\Item\Collection
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _assignProducts(): self
    {
        \Magento\Framework\Profiler::start('QUOTE:' . __METHOD__, ['group' => 'QUOTE', 'method' => __METHOD__]);
        $productCollection = $this->_productCollectionFactory->create()->setStoreId(
            $this->getStoreId()
        )->addIdFilter(
            $this->_productIds
        )->addAttributeToSelect(
            $this->_quoteConfig->getProductAttributes()
        );
        $this->skipStockStatusFilter($productCollection);
        $productCollection->addOptionsToResult()->addStoreFilter()->addUrlRewrite();

        $this->_eventManager->dispatch(
            'prepare_catalog_product_collection_prices',
            ['collection' => $productCollection, 'store_id' => $this->getStoreId()]
        );
        $this->_eventManager->dispatch(
            'sales_quote_item_collection_products_after_load',
            ['collection' => $productCollection]
        );

        foreach ($this as $item) {
            /** @var ProductInterface $product */
            $product = $productCollection->getItemById($item->getProductId());
            try {
                /** @var QuoteItem $item */
                $parentItem = $item->getParentItem();
                $parentProduct = $parentItem ? $parentItem->getProduct() : null;
            } catch (NoSuchEntityException $exception) {
                $parentItem = null;
                $parentProduct = null;
                $this->_logger->error($exception);
            }
            $qtyOptions = [];
            if ($this->isValidProduct($product) && (!$parentItem || $this->isValidProduct($parentProduct))) {
                $product->setCustomOptions([]);
                $optionProductIds = $this->getOptionProductIds($item, $product, $productCollection);
                foreach ($optionProductIds as $optionProductId) {
                    $qtyOption = $item->getOptionByCode('product_qty_' . $optionProductId);
                    if ($qtyOption) {
                        $qtyOptions[$optionProductId] = $qtyOption;
                    }
                }
            } else {
                $item->isDeleted(true);
                $this->recollectQuote = true;
            }
            if (!$item->isDeleted()) {
                $item->setQtyOptions($qtyOptions)->setProduct($product);
                $item->checkData();
            }
        }
        if ($this->recollectQuote && $this->_quote) {
            $this->_quote->setTotalsCollectedFlag(false);
        }
        \Magento\Framework\Profiler::stop('QUOTE:' . __METHOD__);

        return $this;
    }
    /**
     * Prevents adding stock status filter to the collection of products.
     *
     * @param ProductCollection $productCollection
     * @return void
     *
     * @see \Magento\CatalogInventory\Helper\Stock::addIsInStockFilterToCollection
     */
    private function skipStockStatusFilter(ProductCollection $productCollection): void
    {
        $productCollection->setFlag('has_stock_status_filter', true);
    }
}

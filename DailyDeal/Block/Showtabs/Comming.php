<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MW\DailyDeal\Block\Showtabs;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Url\Helper\Data;
use phpDocumentor\Reflection\Types\Self_;

class Comming extends \Magento\Catalog\Block\Product\ListProduct
{
    const STATUS_ENABLED = '1';
    const STATUS_EXPIRE_FALSE = '1';
    /**
     * @var \MW\DailyDeal\Model\ResourceModel\Dailydeal\CollectionFactory
     */
    protected $dailydealCollectionFactory;
    protected $dealSchecdulerCollectionFactory;
    protected $dealSchecdulerModel;
    protected $dailydealModel;
    public $helperConfig;
    public $helperDailyDeal;
    public $filterGroup;
    public $filterBuilder;
    public $productStatus;
    public $productVisibility;
    public $productRepository;
    public $searchCriteria;
    public $productCollectionFactory;
    public $productModel;
    protected $dealRepository;
    protected $pricingHelper;
    protected $date;
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        \MW\DailyDeal\Model\ResourceModel\Dailydeal\CollectionFactory $dailydealCollectionFactory,
        \MW\DailyDeal\Model\ResourceModel\Dealscheduler\CollectionFactory $dealSchedulerCollectionFactory,
        \MW\DailyDeal\Model\Dealscheduler $dealSchecdulerModel,
        \MW\DailyDeal\Model\Dailydeal $dailydealModel,
        \MW\DailyDeal\Helper\Config $helperConfig,
        \MW\DailyDeal\Helper\Data $helperDailyDeal,
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Api\SearchCriteriaInterface $criteria,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product $productModel,
        \MW\DailyDeal\Model\DealRepository $dealRepository,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        array $data = []
    ) {
        $this->dailydealCollectionFactory = $dailydealCollectionFactory;
        $this->dealSchecdulerCollectionFactory = $dealSchedulerCollectionFactory;
        $this->dealSchecdulerModel = $dealSchecdulerModel;
        $this->dailydealModel = $dailydealModel;
        $this->helperConfig = $helperConfig;
        $this->helperDailyDeal = $helperDailyDeal;
        $this->filterGroup = $filterGroup;
        $this->filterBuilder = $filterBuilder;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->productRepository = $productRepository;
        $this->searchCriteria = $criteria;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productModel = $productModel;
        $this->dealRepository = $dealRepository;
        $this->pricingHelper = $pricingHelper;
        $this->date = $date;
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }

    /**
     * Retrieve loaded category collection
     *
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $currenttime = $this->date->date()->format('Y-m-d H:i:s');
            $collection = $this->dailydealModel
                ->getCollection()
                ->addFieldToFilter('status', self::STATUS_ENABLED)
                ->addFieldToFilter('expire', self::STATUS_EXPIRE_FALSE);
//                ->addFieldToFilter('store_view', array(array('like' => '%' . $storeId . '%'), array('like' => '0')))
//                ->addProductStatusFilter($store_id);

            $collection->addFieldToFilter('start_date_time', array('to' => $currenttime))
                ->addFieldToFilter('end_date_time', array('from' => $currenttime));
            $collection->getSelect()->where("
                    (end_date_time <= '{$currenttime}') OR (((expire = '". self::STATUS_EXPIRE_FALSE . "')))
                ");
            $collection->getSelect()->joinLeft(
                ['stock' => 'cataloginventory_stock_item'],
                'stock.product_id = main_table.product_id',
                array('stock.qty', 'stock.is_in_stock')
            );
            $listProductIdDeal = $this->dailydealModel->getListPastDeal();
            $col = $this->productCollectionFactory->create()->addFieldToFilter(
                'entity_id',
                ['in' => $listProductIdDeal]
            );
            return $col;
        }
        return $this->_productCollection;
    }

    public function loadByProductId($productId)
    {
        return $this->dailydealModel->getCommingDealByProductId($productId);
    }

    public function getConfigIsShowImageCatalogList()
    {
        return $this->helperConfig->getConfigIsShowImageCatalogList();
    }

    public function getConfigDisplayQuantity()
    {
        return $this->helperConfig->getConfigDisplayQuantity();
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCommingDeals()
    {
        $listProductIdDeal = $this->dailydealModel->getListCommingDeal();
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollectionFactory->create()->addAttributeToSelect('*');
//        $joinConditions = 'e.entity_id = s.product_id AND s.status = 0';
//        $collection->getSelect()->join(
//            ['s' => 'mw_dailydeal'],
//            $joinConditions,
//            []
//        )->columns("s.dailydeal_price");
        if ($this->getRequest()->getParam('product_list_order') == 'name') {
            if ($this->getRequest()->getParam('product_list_dir') == 'desc') {
                $collection->addAttributeToSelect('*')->setOrder('name', 'DESC');
            } else {
                $collection->addAttributeToSelect('*')->setOrder('name', 'ASC');
            }
        }

        if ($this->getRequest()->getParam('product_list_order') == 'price') {
            if ($this->getRequest()->getParam('product_list_dir') == 'desc') {
                $collection->addAttributeToSelect('price')->setOrder('price', 'DESC');
            } else {
                $collection->addAttributeToSelect('price')->setOrder('price', 'ASC');
            }
        }

        //get values of current page. if not the param value then it will set to 1
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        //get values of current limit. if not the param value then it will set to 1
        $pageSize = ($this->getRequest()
            ->getParam('product_list_limit')) ? $this->getRequest()->getParam('product_list_limit') : 9;

        $collection->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', ['in' => $listProductIdDeal])
            ->getSelect();
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        return $collection;
    }

    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();
        $toolbar->setAvailableOrders(array());  /** clear */
        $toolbar->addOrderToAvailableOrders('name', __('Name'));
        $toolbar->addOrderToAvailableOrders('price', __('Price'));
        $toolbar->setDefaultDirection('asc');
        $collection = $this->_getProductCollection();
        $toolbar->setCollection($collection);
        $this->setChild('toolbar', $toolbar);
    }

    public function aroundGetAvailableOrders(
        \Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
        \Closure $proceed
    ) {
        $returnValue = $proceed();
        unset($returnValue['price']);
        $returnValue['priceDesc'] = 'price - high to low';
        $returnValue['priceAsc'] = 'price - low to high';
        return $returnValue;
    }

    public function aroundSetCollection(
        \Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
        \Closure $proceed,
        $collection
    ) {
        $returnValue = $proceed($collection);
        if ($subject->getCurrentOrder() == 'priceHighToLow') {
            $collection->addAttributeToSelect('*')->setOrder('price', 'ASC');
            $collection->load();
        }
        return $collection;
    }

    public function addPriceWithCurrency($price)
    {
        return $this->pricingHelper->currency($price, true, false);
    }

    public function getLayOutConfig()
    {
        return $this->helperConfig->getConfigPageLayout();
    }
}

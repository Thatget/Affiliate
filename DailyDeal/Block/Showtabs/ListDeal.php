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

class ListDeal extends \Magento\Catalog\Block\Product\ListProduct
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
    protected $date;
    protected $dealRepository;
    protected $business;
    protected $resource;
    protected $asset;

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
        \MW\DailyDeal\Model\DealRepository $dealRepository,
        \MW\DailyDeal\Model\Business $business,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\View\Asset\Repository $asset,
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
        $this->dealRepository = $dealRepository;
        $this->business = $business;
        $this->date = $date;
        $this->resource = $resource;
        $this->asset = $asset;
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
        if ($this->_productCollection === null) {
            $listProductIdDeal = $this->dailydealModel->getListActiveDeal();
            $col = $this->productCollectionFactory
                ->create()->addFieldToFilter('entity_id', ['in' => $listProductIdDeal]);
            return $col;
        }
        return $this->_productCollection;
    }

    public function loadByProductId($productId)
    {
        return $this->dailydealModel->loadByProductId($productId);
    }

    public function loadDealActiveByProductId($productId)
    {
        return $this->dailydealModel->loadActiveDealByProductId($productId);
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
    public function getProducts()
    {
        $this->business->updateStatusDeals();
        $listProductIdDeal = $this->dailydealModel->getListActiveDeal();
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollectionFactory->create();
        //get values of current page. if not the param value then it will set to 1
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        //get values of current limit. if not the param value then it will set to 1
        $pageSize = ($this->getRequest()->getParam('product_list_limit')) ? $this->getRequest()
            ->getParam('product_list_limit') : 9;
        $collection->addAttributeToSelect('*');
        $joinConditions = 'e.entity_id = s.product_id AND s.status = 1';
        $collection->getSelect()->join(
            ['s' => 'mw_dailydeal'],
            $joinConditions,
            []
        )->columns("s.dailydeal_price");
//        $collection->addFieldToFilter('entity_id', ['in' => $listProductIdDeal]);
//        $collection->getSelect()->group('e.entity_id');
//        $collection->addAttributeToSelect('*')
////            ->addFieldToFilter('entity_id', ['in' => $listProductIdDeal])
//            ->addFieldToFilter('entity_id', array('eq' => $listProductIdDeal))
//            ->getSelect();
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        if ($this->getRequest()->getParam('product_list_order') == 'name') {
            if ($this->getRequest()->getParam('product_list_dir') == 'desc') {
                $collection->addAttributeToSelect('*')->setOrder('name', 'DESC');
            } else {
                $collection->addAttributeToSelect('*')->setOrder('name', 'ASC');
            }
        }

        if ($this->getRequest()->getParam('product_list_order') == 'end_date_time') {
            if ($this->getRequest()->getParam('product_list_dir') == 'desc') {
                $collection->getSelect()->order('s.end_date_time DESC');
            } else {
                $collection->getSelect()->order('s.end_date_time ASC');
            }
        }

        if ($this->getRequest()->getParam('product_list_order') == 'deal_price') {
            if ($this->getRequest()->getParam('product_list_dir') == 'desc') {
                $collection->getSelect()->order('s.dailydeal_price DESC');
            } else {
                $collection->getSelect()->order('s.dailydeal_price ASC');
            }
        }

        if ($this->getRequest()->getParam('product_list_order') == null) {
            $collection->getSelect()->order('s.end_date_time ASC');
        }

        return $collection;
    }

    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();
        $toolbar->setAvailableOrders(array());  /** clear */
        $toolbar->addOrderToAvailableOrders('end_date_time', __('Deal Time'));
        $toolbar->addOrderToAvailableOrders('name', __('Name'));
        $toolbar->addOrderToAvailableOrders('deal_price', __('Deal Price'));
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

    public function getConfigSchemeColor()
    {
        return $this->helperConfig->getConfigSchemeColor();
    }

    public function getConfigCountdownColor()
    {
        return $this->helperConfig->getConfigCountDownColor();
    }

    public function getConfigHighlightColor()
    {
        return $this->helperConfig->getConfigHightLightColor();
    }

    public function getTimeStamp()
    {
        return $this->date->scopeTimeStamp();
    }

    public function getLayOutConfig()
    {
        return $this->helperConfig->getConfigPageLayout();
    }

    public function increateFeaturedView($dealId)
    {
        $this->business->increateFeaturedView($dealId);
    }

    public function getConfigUseCustomLableDiscount()
    {
        return $this->helperConfig->getConfigUseCustomLableDiscount();
    }

    public function moduleEnable()
    {
        if (!$this->helperConfig->isEnabled()) {
            return false;
        }
        return true;
    }

    /**
     * @param string $asset
     * @return string
     */
    //This function will be used to get the css/js file.
    public function getAssetUrl($asset)
    {
        $assetRepository = $this->asset;
        return $assetRepository->createAsset($asset)->getUrl();
    }

    public function getStoreBaseUrl()
    {
        $this->_storeManager->getStore()->getBaseUrl();
    }
}

<?php

namespace MW\DailyDeal\Controller\Adminhtml;

use Magento\Framework\Exception\LocalizedException;

abstract class AbstractAction extends \Magento\Backend\App\Action
{
    /**
     * param id for crud action : edit,delete,save.
     */
    const PARAM_CRUD_ID = 'entity_id';

    /**
     * registry name.
     */
    const REGISTRY_NAME = 'registry_model';

    /**
     * main model class name.
     *
     * @var string
     */
    protected $_mainModelName;

    /**
     * main collection class name.
     *
     * @var string
     */
    protected $_mainCollectionName;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Escaper.
     *
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $_massActionFilter;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $_mediaDirectory;


    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $_backendHelperJs;
    /**
     * @var \Magento\Backend\App\Action\Context
     */
    private $context;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    protected $dailydealFactory;

    protected $localeDate;

    protected $business;

    protected $date;
    /**
     * @var \MW\DailyDeal\Model\Dealschedulerproduct
     */
    protected $dealSchedulerProduct;
    protected $dealSchedulerProductFactory;
    protected $productBuilder;
    protected $resultLayoutFactory;
    protected $dealSchedulerFactory;
    protected $stdTimezone;

    /**
     * AbstractAction constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Ui\Component\MassAction\Filter $massActionFilter
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Helper\Js $backendHelperJs
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \MW\DailyDeal\Model\DailydealFactory $dailydeal
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \MW\DailyDeal\Model\Dealschedulerproduct $dealSchedulerProduct
     * @param \MW\DailyDeal\Model\DealschedulerproductFactory $dealSchedulerProductFactory
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \MW\DailyDeal\Model\Dealscheduler $dealSchedulerFactory
     * @param \MW\DailyDeal\Model\Business $business
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date
     * @param null $mainModelName
     * @param null $mainCollectionName
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Escaper $escaper,
        \Magento\Ui\Component\MassAction\Filter $massActionFilter,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Helper\Js $backendHelperJs,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \MW\DailyDeal\Model\DailydealFactory $dailydeal,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \MW\DailyDeal\Model\Dealschedulerproduct $dealSchedulerProduct,
        \MW\DailyDeal\Model\DealschedulerproductFactory $dealSchedulerProductFactory,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \MW\DailyDeal\Model\Dealscheduler $dealSchedulerFactory,
        \MW\DailyDeal\Model\Business $business,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        $mainModelName = null,
        $mainCollectionName = null
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_massActionFilter = $massActionFilter;
        $this->_escaper = $escaper;
        $this->_backendHelperJs = $backendHelperJs;
        $this->_mainModelName = $mainModelName;
        $this->_mainCollectionName = $mainCollectionName;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dailydealFactory = $dailydeal;
        $this->localeDate = $localeDate;
        $this->dealSchedulerProduct = $dealSchedulerProduct;
        $this->dealSchedulerProductFactory = $dealSchedulerProductFactory;
        $this->productBuilder = $productBuilder;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->dealSchedulerFactory = $dealSchedulerFactory;
        $this->business = $business;
        $this->stdTimezone = $timezone;
        $this->date = $date;
        $this->context = $context;
    }

    /**
     * create m.
     *
     * @return \Magento\Framework\Model\AbstractModel
     *
     * @throws LocalizedException
     */
    protected function _createMainModel()
    {
        /** @var \Magento\Framework\Model\AbstractModel $model */
        $model = $this->_objectManager->create($this->_mainModelName);

        if (!$model instanceof \Magento\Framework\Model\AbstractModel) {
            throw new LocalizedException(
                __('%1 isn\'t instance of Magento\Framework\Model\AbstractModel', get_class($model))
            );
        }

        return $model;
    }
    public function dealSchedulerFactory()
    {
        return $this->dealSchedulerFactory;
    }
    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     *
     * @throws LocalizedException
     */
    protected function _createMainCollection()
    {
        /** @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection */
        $collection = $this->_objectManager->create($this->_mainCollectionName);
        if (!$collection instanceof \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection) {
            throw new LocalizedException(
                __(
                    '%1 isn\'t instance of Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection',
                    get_class($collection)
                )
            );
        }

        return $collection;
    }

    /**
     * get back result redirect after add/edit.
     *
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function _getBackResultRedirect(\Magento\Backend\Model\View\Result\Redirect $resultRedirect, $paramCrudId = null)
    {
        switch ($this->getRequest()->getParam('back')) {
            case 'edit':
                $resultRedirect->setPath(
                    '*/*/edit',
                    [
                        static::PARAM_CRUD_ID => $paramCrudId,
                        '_current' => true,
                    ]
                );
                break;
            case 'new':
                $resultRedirect->setPath('*/*/new');
                break;
            default:
                $resultRedirect->setPath('*/*/indexFilterAll');
        }

        return $resultRedirect;
    }

    /**
     * Check if admin has permissions to visit related pages.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_DailyDeal::dealitems_indexdeals');
    }

    public function getJsonFactory()
    {
        return $this->resultJsonFactory;
    }
}

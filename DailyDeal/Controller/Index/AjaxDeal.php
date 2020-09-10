<?php

namespace MW\DailyDeal\Controller\Index;

use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Session as CustomerModelSession;

class AjaxDeal extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */

    protected $helperConfig;
    protected $helperDailyDeal;
    protected $logger;
    protected $productMetadata;
    protected $inlineTranslation;
    protected $escaper;
    protected $transportBuilder;
    protected $dailyDealModel;
    protected $date;
    protected $productRepository;

    /**
     * Action constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \MW\DailyDeal\Helper\Config $helperConfig
     * @param \MW\DailyDeal\Helper\Data $helperDailyDeal
     * @param \MW\DailyDeal\Model\Dailydeal $dailyDealModel
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \MW\DailyDeal\Helper\Config $helperConfig,
        \MW\DailyDeal\Helper\Data $helperDailyDeal,
        \MW\DailyDeal\Model\Dailydeal $dailyDealModel,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        parent::__construct($context);
        $this->helperConfig = $helperConfig;
        $this->helperDailyDeal = $helperDailyDeal;
        $this->dailyDealModel = $dailyDealModel;
        $this->logger = $logger;
        $this->productMetadata = $productMetadata;
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->date = $date;
        $this->productRepository = $productRepository;
        $this->jsonHelper = $jsonHelper;
    }
    /**
     * Execute action.
     */
    public function execute()
    {
        $dealId = (int)$this->getRequest()->getParam('dealId');
        $deal = $this->dailyDealModel->load($dealId);

        //disable deal
        $deal->setStatus(\MW\DailyDeal\Model\Status::STATUS_TIME_ENDED);

        //disable product
        $isDisableProductAfterFinish = $deal->getData('disable_product_after_finish');
        if ($isDisableProductAfterFinish == 1) {
            $productId = $deal->getData('product_id');
            $product = $this->productRepository->getById($productId);
            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
            $this->productRepository->save($product);
            $deal->setData('disable_product_after_finish', \MW\DailyDeal\Model\Status::STATUS_PRODUCT_DONE);
        }
        $this->messageManager->addNotice(
            __('This Deal Ended')
        );
        $deal->save();
    }
}

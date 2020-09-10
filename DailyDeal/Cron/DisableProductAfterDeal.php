<?php

namespace MW\DailyDeal\Cron;

class DisableProductAfterDeal
{
    protected $dailyDealModel;
    protected $productDealModel;
    protected $productRepository;
    protected $logger;
    protected $helperConfig;
    protected $business;

    /**
     * @param \MW\DailyDeal\Model\Dailydeal $dailyDealModel
     * @param \MW\DailyDeal\Model\ProductDeal $productDealModel
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \MW\DailyDeal\Helper\Config $helperConfig
     * @param \MW\DailyDeal\Model\Business $business
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \MW\DailyDeal\Model\Dailydeal $dailyDealModel,
        \MW\DailyDeal\Model\ProductDeal $productDealModel,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \MW\DailyDeal\Helper\Config $helperConfig,
        \MW\DailyDeal\Model\Business $business,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->dailyDealModel = $dailyDealModel;
        $this->productDealModel = $productDealModel;
        $this->productRepository = $productRepository;
        $this->helperConfig = $helperConfig;
        $this->business = $business;
        $this->logger = $logger;
    }

    public function execute()
    {
        $this->logger->info('Cron Disable Deal Product');
        if (!$this->helperConfig->isEnabled()) {
            return;
        }

        // disable Product if deal expire time
        $this->business->autoDisableProduct();
        $this->business->updateStatusDeals();
    }
}

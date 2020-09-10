<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MW\DailyDeal\Model;

use MW\DailyDeal\Model\ResourceModel\Dealscheduler\Collection;
use MW\DailyDeal\Model\ResourceModel\Dealscheduler\CollectionFactory;
use MW\DailyDeal\Model\Dealscheduler;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class DataProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var Dealscheduler $deal */
        foreach ($items as $deal) {
            $deal->load($deal->getId());
            $this->loadedData[$deal->getId()]['setting_information'] = $deal->getData();
        }

        $data = $this->dataPersistor->get('deal_scheduler');
        if (!empty($data)) {
            $deal = $this->collection->getNewEmptyItem();
            $deal->setData($data);
            $this->loadedData[$deal->getId()]['setting_information'] = $deal->getData();
            $this->dataPersistor->clear('deal_scheduler');
        }

        return $this->loadedData;
    }
}

<?php

namespace MW\DailyDeal\Model;

use MW\DailyDeal\Api\Data\DealInterface;
use MW\DailyDeal\Api\Data\DealSearchResultsInterfaceFactory as DealSearchResultsInterfaceFactory;
use MW\DailyDeal\Model\DailydealFactory as DealFactory;
use MW\DailyDeal\Api\DailydealRepositoryInterface;
use MW\DailyDeal\Model\ResourceModel\Dailydeal as ResourceDeal;
use MW\DailyDeal\Model\ResourceModel\Dailydeal\CollectionFactory as DealCollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

class DealRepository implements DailydealRepositoryInterface
{
    /**
     * @var ResourceDeal
     */
    protected $resource;

    /**
     * @var DealFactory
     */
    protected $dealFactory;

    /**
     * @var DealCollectionFactory
     */
    protected $dealCollectionFactory;

    /**
     * @var DealSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var DealInterface[]
     */
    protected $instances = [];

    /**
     * @param ResourceDeal $resource
     * @param \MW\DailyDeal\Model\DailydealFactory $dealFactory
     * @param DealCollectionFactory $deaCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceDeal $resource,
        DealCollectionFactory $dealCollectionFactory,
        DealFactory $dealFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->dealCollectionFactory = $dealCollectionFactory;
        $this->dealFactory = $dealFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param DealInterface $deal
     * @return DealInterface
     * @throws CouldNotSaveException
     */
    public function save(DealInterface $deal)
    {
        try {
            $this->resource->save($deal);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        unset($this->instances[$deal->getId()]);
        return $deal;
    }

    /**
     * @param int $dealId
     * @return DealInterface
     * @throws NoSuchEntityException
     */
    public function getById($dealId)
    {
        if (!isset($this->instances[$dealId])) {
            $deal = $this->dealFactory->create();
            $this->resource->load($deal, $dealId);
            if (!$deal->getId()) {
                throw new NoSuchEntityException(__('Deal with id "%1" does not exist.', $dealId));
            }
            $this->instances[$dealId] = $deal;
        }

        return $this->instances[$dealId];
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return \MW\DailyDeal\Api\Data\DealSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \MW\DailyDeal\Model\ResourceModel\Dailydeal\Collection $collection */
        $collection = $this->dealCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \MW\DailyDeal\Api\Data\DealSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param DealInterface $deal
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(DealInterface $deal)
    {
        try {
            $dealId = $deal->getId();
            $this->resource->delete($deal);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        unset($this->instances[$dealId]);
        return true;
    }

    /**
     * @param int $dealId
     * @return bool true on success
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($dealId)
    {
        return $this->delete($this->getById($dealId));
    }
}

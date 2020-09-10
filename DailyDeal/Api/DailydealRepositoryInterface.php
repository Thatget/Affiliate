<?php
namespace MW\DailyDeal\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface DailydealRepositoryInterface
{
    /**
     * Save deal
     *
     * @param \MW\DailyDeal\Api\Data\DealInterface $dailydeal
     * @return \MW\DailyDeal\Api\Data\DealInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\DealInterface $dailydeal);

    /**
     * Retrieve deal
     *
     * @param int $dailydealId
     * @return \MW\DailyDeal\Api\Data\DealInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($dailydealId);

    /**
     * Retrieve deals matching the specified criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MW\DailyDeal\Api\Data\DealInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete deal
     *
     * @param \MW\DailyDeal\Api\Data\DealInterface $deal
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\DealInterface $deal);

    /**
     * Delete deal by ID
     *
     * @param int $dealId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($dealId);
}

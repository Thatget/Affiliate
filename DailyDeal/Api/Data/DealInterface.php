<?php
namespace MW\DailyDeal\Api\Data;

interface DealInterface
{
    /**
     * Constants for keys of data array.
     * `website_id`,  `store_ids`,  `customer_group_ids`,  `promo`,  `featured`,  `disableproduct`,
    `requiredlogin`,  `impression`, `order_id`,  `store_view`,`thread`
     */
    const DAILYDEAL_ID = 'dailydeal_id';
    const PRODUCT_ID = 'product_id';
    const CUR_PRODUCT = 'cur_product';
    const PRODUCT_SKU = 'product_sku';
    const PRODUCT_PRICE = 'product_price';
    const DISCOUNT = 'discount';
    const DISCOUNT_TYPE = 'discount_type';
    const START_DATE_TIME = 'start_date_time';
    const END_DATE_TIME = 'end_date_time';
    const DAILYDEAL_PRICE = 'dailydeal_price';
    const DEAL_QTY = 'deal_qty';
    const STARTUS = 'status';
    const DESCRIPTION = 'description';
    const SOLD_QTY = 'sold_qty';
    const LIMIT_CUSTOMER = 'limit_customer';
    const DISABLE_PRODUCT_AFTER_FINISH = 'disable_product_after_finish';
    const EXPIRE = 'expire';
    const ACTIVE = 'active';
    const DEAL_SCHEDULER_ID = 'deal_scheduler_id';

    /**
     * Get id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    public function getProductId();
    public function setProductId($productId);

    public function getCurProduct();
    public function setCurProduct($curProduct);

    public function getProductSku();
    public function setProductSku($productSku);

    public function getProductPrice();
    public function setProductPrice($productPrice);

    public function getDiscount();
    public function setDiscount($discount);

    public function getDiscountType();
    public function setDiscountType($discountType);

    public function getStartDateTime();
    public function setStartDateTime($startDateTime);
//
    public function getEndDateTime();
    public function setEndDateTime($endDateTime);
//
//    public function getDailydealPrice();
//    public function setDailydealPrice();
//
    public function getDealQty();
    public function setDealQty($dealQty);
//
//    public function getStatus();
//    public function setStatus();
//
//    public function getDescription();
//    public function setDescription();
//
    public function getSoldQty();
    public function setSoldQty($soldQty);
//
//    public function getLimitCustomer();
//    public function setLimitCustomer();
//
//    public function getDisableProductAfterFinish();
//    public function setDisableProductAfterFinish();
//
//    public function getExpire();
//    public function setExpire();
//
//    public function getActive();
//    public function setActive();
//
//    public function getDealSchedulerId();
//    public function setDealSchedulerId();
}

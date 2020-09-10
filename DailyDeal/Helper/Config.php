<?php
namespace MW\DailyDeal\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Escaper;

class Config
{
    const XML_PATH_ENABLED = 'mw_dailydeal/group_general/active';
    const XML_PATH_SHOW_TOP_LINK = 'mw_dailydeal/group_general/show_top_link';
    const XML_PATH_SHOW_TODAY_DEAL_BLOCK = 'mw_dailydeal/group_general/sidebardeal';
    const XML_PATH_SHOW_WEEK_DEAL_BLOCK = 'mw_dailydeal/group_general/calendar';
    const XML_PATH_SHOW_ACTIVE_DEAL_BLOCK = 'mw_dailydeal/group_general/sidebaractive';
    const XML_PATH_DAILY_PAGE_LAYOUT = 'mw_dailydeal/group_general/deallayout';
    const XML_PATH_NUMBER_PRODUCT_PER_ROW = 'mw_dailydeal/group_general/columncount';
    const XML_PATH_STATUS_ORDER_TO_SOLD = 'mw_dailydeal/group_general/status_to_sold';

    const XML_PATH_TODAY_DEAL_SHOW_DETAIL = 'mw_dailydeal/deal_display/today_deal_show_detail';
    const XML_PATH_SHOW_QTY = 'mw_dailydeal/deal_display/showqty';
    const XML_PATH_SHOW_DESCRIPTION = 'mw_dailydeal/deal_display/show_description';
    const XML_PATH_IMAGE_CATALOG_LIST = 'mw_dailydeal/deal_display/catalog_list_show_image';

    const XML_PATH_NUMBER_DEAL_ACTIVE = 'mw_dailydeal/deal_display/numberactive';
    const XML_PATH_SORT_BY = 'mw_dailydeal/deal_display/sort_by';
    const XML_PATH_USE_CUSTOM_LABEL_DISCOUNT = 'mw_dailydeal/deal_display/show_custom_label_discount';

    const XML_PATH_DEAL_QTY_ON_CATALOG_PAGE = 'mw_dailydeal/titles_messages/deal_qty_on_catalog_page';
    const XML_PATH_PRODUCT_DETAIL_LABEL = 'mw_dailydeal/titles_messages/productdetaillabel';
    const XML_PATH_DEAL_QTY_ON_PRODUCT_PAGE = 'mw_dailydeal/titles_messages/deal_qty_on_product_page';
    const XML_PATH_TODAY_DEAL_BLOCK_TITLE = 'mw_dailydeal/titles_messages/title';

    const XML_PATH_SCHEME_COLOR = 'mw_dailydeal/deal_colors/schemecolor';
    const XML_PATH_COUNTDOWN_COLOR = 'mw_dailydeal/deal_colors/countdowncolor';
    const XML_PATH_HIGHT_LIGHT_COLOR = 'mw_dailydeal/deal_colors/highlight_color';

    const XML_PATH_NOTIFY_ADMIN = 'mw_dailydeal/admin_notification/notify_admin';
    const XML_PATH_ADMIN_EMAIL = 'mw_dailydeal/admin_notification/admin_email';

    const XML_PATH_EMAIL_TEMPLATE_NO_DEAL = 'mw_dailydeal/global_variable/template_id_no_deal';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Escaper $escaper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Escaper $escaper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->escaper = $escaper;
    }

    /**
     * Set a specified store ID value
     *
     * @param int $store
     * @return $this
     */
    public function setStoreId($store)
    {
        $store = $store != null ? $store : 0;
        $this->storeId = $store;
        return $this;
    }

    /**
     * Check if Minimum Advertised Price is enabled
     *
     * @return bool
     * @api
     */
    public function isEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function isShowTopLink()
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_SHOW_TOP_LINK,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigTodayDealShowDetail()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TODAY_DEAL_SHOW_DETAIL,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigDisplayQuantity()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SHOW_QTY,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigDisplayDescription()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SHOW_DESCRIPTION,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigIsShowImageCatalogList()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_IMAGE_CATALOG_LIST,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigDealQtyOnCatalogPage()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEAL_QTY_ON_CATALOG_PAGE,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigDealQtyOnProductPage()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEAL_QTY_ON_PRODUCT_PAGE,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigProductDetailLabel()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_DETAIL_LABEL,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigSchemeColor()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SCHEME_COLOR,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigCountDownColor()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COUNTDOWN_COLOR,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigHightLightColor()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_HIGHT_LIGHT_COLOR,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigNotifyAdmin()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_NOTIFY_ADMIN,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigAdminEmail()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADMIN_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigEmailTemplateNoDeal()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_TEMPLATE_NO_DEAL,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigNumberDealActive()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_NUMBER_DEAL_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigTodayDealBlockTitle()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TODAY_DEAL_BLOCK_TITLE,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigSortBy()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SORT_BY,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigShowTodayDealBlock()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SHOW_TODAY_DEAL_BLOCK,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigShowWeekDealBlock()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SHOW_WEEK_DEAL_BLOCK,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigShowActiveDealBlock()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SHOW_ACTIVE_DEAL_BLOCK,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigPageLayout()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DAILY_PAGE_LAYOUT,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigUseCustomLableDiscount()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_USE_CUSTOM_LABEL_DISCOUNT,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    public function getConfigStatusOrderToSold()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_STATUS_ORDER_TO_SOLD,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }
}

<?php

namespace MW\DailyDeal\Block;

class Link extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var \MW\DailyDeal\Helper\Config
     */
    protected $helperConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \MW\DailyDeal\Helper\Config $helperConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \MW\DailyDeal\Helper\Config $helperConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helperConfig = $helperConfig;
    }

    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $check = ($this->helperConfig->isEnabled() && $this->helperConfig->isShowTopLink());
        return $check ? parent::_toHtml() : '';
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl('dailydeal');
    }
}

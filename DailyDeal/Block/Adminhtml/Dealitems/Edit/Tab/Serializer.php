<?php
namespace MW\DailyDeal\Block\Adminhtml\Dealitems\Edit\Tab;

class Serializer extends \Magento\Backend\Block\Widget\Grid\Serializer
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $backendUrl;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        array $data = []
    ) {
        $this->backendUrl = $backendUrl;
        parent::__construct($context, $jsonEncoder, $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->setUseAjax(true);
        $this->setTemplate('MW_DailyDeal::serializer.phtml');
    }

    public function getChangeProductUrl()
    {
        return $this->backendUrl->getUrl('*/*/changeProduct', ['_current' => true]);
    }
}

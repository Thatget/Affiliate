<?php

namespace MW\Affiliate\Block\Adminhtml\Renderer;

class Customerinvited extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
	/**
	 * @var \Magento\Customer\Model\CustomerFactory
	 */
	protected $_customerFactory;

	/**
	 * @param \Magento\Backend\Block\Context $context
	 * @param \Magento\Customer\Model\CustomerFactory $customerFactory
	 * @param array $data
	 */
	public function __construct(
		\Magento\Backend\Block\Context $context,
		\Magento\Customer\Model\CustomerFactory $customerFactory,
		array $data = []
	) {
		$this->_customerFactory = $customerFactory;
		parent::__construct($context, $data);
	}

	/**
	 * @param \Magento\Framework\DataObject $row
	 * @return string
	 */
	public function render(\Magento\Framework\DataObject $row)
	{
    	if (empty($row['customer_invited'])) {
			return '';
		}

    	$email = $this->_customerFactory->create()->load($row['customer_invited'])->getEmail();

    	return $email;
    }
}

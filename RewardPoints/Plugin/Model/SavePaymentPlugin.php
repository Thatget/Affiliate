<?php
namespace MW\RewardPoints\Plugin\Model;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Checkout\Model\PaymentInformationManagement;
use Magento\Framework\Exception\AbstractAggregateException;
use MW\RewardPoints\Block\Checkout\Cart\Totals\Rewardpoints;

class SavePaymentPlugin extends Rewardpoints
{
    /**
     * @var \MW\RewardPoints\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \MW\RewardPoints\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    )
    {
        $this->_customerFactory = $customerFactory;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $checkoutSession, $data);
    }

    /**
     * @param PaymentInformationManagement $subject
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     *
     * @return array
     *
     * @throws AbstractAggregateException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSavePaymentInformation(
        PaymentInformationManagement $subject,
        int $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ): array {
        $quote = $this->getCheckoutSession()->getQuote();
        //$amountUsed1 = $quote->getMwRewardpointDiscountShow();
        $amountUsed2 = $quote->getMwRewardpoint();
        $amountTotal = $this->getCurrentRewardPoints();
        if (($amountTotal-$amountUsed2) < 0){
            throw new \Magento\Framework\Exception\CouldNotSaveException(__("Your point amount has just been updated "));
            //throw new LocalizedException(__("You need to add more 'This is my custom error !!!!!'"));
        }else {
            // Add your logic before Save Payment information
            return [$cartId, $paymentMethod, $billingAddress];
        }
    }

    public function getCurrentRewardPoints()
    {
        $customer = $this->_customerFactory->create()->load(
            $this->_customerSession->getCustomer()->getId()
        );

        return $customer->getMwRewardPoint();
    }
}

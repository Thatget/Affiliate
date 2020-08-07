<?php

namespace MW\Affiliate\Observer;

use Magento\Framework\Event\ObserverInterface;

class SaveAffiliateCreditHistory implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $_redirect;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\Affiliate\Model\CreditcustomerFactory
     */
    protected $_creditcustomerFactory;

    /**
     * @var \MW\Affiliate\Model\CredithistoryFactory
     */
    protected $creditHistoryFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * SaveAffiliateCreditHistory constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\UrlInterface $urlManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param \MW\Affiliate\Model\CreditcustomerFactory $creditcustomerFactory
     * @param \MW\Affiliate\Model\CredithistoryFactory $creditHistoryFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $urlManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \MW\Affiliate\Helper\Data $dataHelper,
        \MW\Affiliate\Model\CreditcustomerFactory $creditcustomerFactory,
        \MW\Affiliate\Model\CredithistoryFactory $creditHistoryFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->_storeFactory = $storeFactory;
        $this->_messageManager = $messageManager;
        $this->_customerFactory = $customerFactory;
        $this->_customerSession = $customerSession;
        $this->_urlManager = $urlManager;
        $this->_redirect = $redirect;
        $this->_dataHelper = $dataHelper;
        $this->_creditcustomerFactory = $creditcustomerFactory;
        $this->creditHistoryFactory = $creditHistoryFactory;
        $this->orderFactory = $orderFactory;

    }

    /**
     * TODO: Re-check referral code
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order_ids = $observer->getOrderIds();
        foreach ($order_ids as $order_id)
        {
            $transaction_collections = $this->_dataHelper->getModelExtensions('\MW\Affiliate\Model\Affiliatetransaction')
                ->getCollection()
                ->addFieldToFilter('status',array('in' => array(\MW\Affiliate\Model\Orderstatus::COMPLETE, \MW\Affiliate\Model\Status::HOLDING)))
                ->addFieldToFilter('order_id',$order_id);
            if(sizeof($transaction_collections) > 0)
            {
                foreach($transaction_collections as $transaction_collection)
                {
                    $customer_id = $transaction_collection->getCustomerInvited();
                    if($customer_id != 0)
                    {
                        $affiliate_customer = $this->_dataHelper->getModelExtensions('\MW\Affiliate\Model\Affiliatecustomers')->load($customer_id);
                        $creditcustomer = $this->_creditcustomerFactory->create()->load($customer_id);
                        $oldTotalCommission = $affiliate_customer->getTotalCommission();
                        $oldCredit = $creditcustomer->getCredit();
                        $amount = $transaction_collection->getTotalCommission();
                        $newTotalCommission = $oldTotalCommission + $amount;
                        $newCredit=$oldCredit + $amount;

                        $newTotalCommission = round($newTotalCommission,2);
                        $newCredit=round($newCredit,2);
                        $amount=round($amount,2);
                        $oldCredit=round($oldCredit,2);

                        $check_holding = $transaction_collection->getStatus();
                        if($check_holding == \MW\Affiliate\Model\Status::HOLDING){
                            $historyData = array(
                                'customer_id'			=> $customer_id,
                                'type_transaction'		=> \MW\Affiliate\Model\Transactiontype::BUY_PRODUCT,
                                //'status'				=> MW_Credit_Model_Orderstatus::COMPLETE,
                                'status'				=> \MW\Affiliate\Model\Orderstatus::HOLDING,
                                'transaction_detail'	=> $order_id,
                                'amount'				=> $amount,
                                'beginning_transaction'=> $oldCredit,
                                'end_transaction'		=> $newCredit,
                                'created_time'			=> date("Y-m-d H:i:s", (new \DateTime())->getTimestamp())
                            );
                            $this->creditHistoryFactory->create()->setData($historyData)->save();

                        }else{

                            $historyData = array(
                                'customer_id'			=> $customer_id,
                                'type_transaction'		=> \MW\Affiliate\Model\Transactiontype::BUY_PRODUCT,
                                'status'				=> \MW\Affiliate\Model\Orderstatus::COMPLETE,
                                'transaction_detail'	=> $order_id,
                                'amount'				=> $amount,
                                'beginning_transaction'=> $oldCredit,
                                'end_transaction'		=> $newCredit,
                                'created_time'			=> date("Y-m-d H:i:s", (new \DateTime())->getTimestamp())
                            );
                            $this->creditHistoryFactory->create()->setData($historyData)->save();


                            /* Don't set new commission for customer because of holding-commission-function */
                            $affiliate_customer->setData('total_commission',$newTotalCommission)->save();
                            $creditcustomer->setData('credit',$newCredit)->save();


                            // gui mail cho khach hang khi khach hang do nhan dc hoa hong tu viec moi thanh vien mua hang
                            $storeId = $this->orderFactory->create()->loadByIncrementId($order_id)->getStoreId();
                            $store_name = $this->_storeManager->getStore($storeId)->getName();
                            $storeCode = $this->_storeManager->getStore($storeId)->getCode();
                            $sender = $this->_dataHelper->getStoreConfig('affiliate/customer/email_sender', $storeCode);
                            $email = $this->_customerFactory->create()->load($customer_id)->getEmail();
                            $name = $this->_customerFactory->create()->load($customer_id)->getName();
                            $teampale = 'affiliate/customer/email_template_credit_balance_changed';
                            $sender_name = $this->_dataHelper->getStoreConfig('trans_email/ident_'.$sender.'/name', $storeCode);
                            $customer_credit_link = $this->_storeManager->getStore($storeId)->getUrl('credit');
                            $data_mail['customer_name'] = $name;
                            $data_mail['transaction_amount'] = $this->_dataHelper->formatMoney($amount);
                            //$data_mail['transaction_amount'] = $this->_dataHelper->formatMoney($amount,true,false);
                            $data_mail['customer_balance'] = $this->_dataHelper->formatMoney($newCredit);
                            //$data_mail['customer_balance'] = Mage::helper('core')->currency($newCredit,true,false);
                            $comment = __('Commission from order #%s',$order_id);
                            $data_mail['transaction_detail'] = $comment;
                            $data_mail['transaction_time'] = date("Y-m-d H:i:s", (new \DateTime())->getTimestamp());
                            $data_mail['sender_name'] = $sender_name;
                            $data_mail['store_name'] = $store_name;
                            $data_mail['customer_credit_link'] = $customer_credit_link;
                            $this->_dataHelper->getModelExtensions('\MW\Affiliate\Helper\Data')->_sendEmailTransactionNew($sender,$email,$name,$teampale,$data_mail,$storeCode);
                        }
                    }
                }
            }
        }

    }
}

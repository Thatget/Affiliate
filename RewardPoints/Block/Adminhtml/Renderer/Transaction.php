<?php

namespace MW\RewardPoints\Block\Adminhtml\Renderer;

use MW\RewardPoints\Model\Status;
use MW\RewardPoints\Model\Type;

class Transaction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \MW\RewardPoints\Model\RewardpointshistoryFactory
     */
    protected $_historyFactory;

    /**
     * @var \MW\RewardPoints\Model\Type
     */
    protected $_type;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
     * @param \MW\RewardPoints\Model\Type $type
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory,
        \MW\RewardPoints\Model\Type $type,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_historyFactory = $historyFactory;
        $this->_type = $type;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        if (empty($row['history_id'])) {
            return '';
        }

        $result = '';
        $miniResult = '';
        $history = $this->_historyFactory->create()->load($row['history_id']);
        $remainingPoint = $history->getPointRemaining();
        $status = $history->getStatus();
        $addPointArray = $this->_type->getAddPointArray();
        $transactionType = $history->getTypeOfTransaction();
        $usedPoint = $history->getAmount() - $remainingPoint;

        if (in_array($transactionType, $addPointArray)
            && $remainingPoint > 0
            && $status == Status::COMPLETE
            && $usedPoint != 0
        ) {
            $miniResult = __('%1 points are available (Used %2 points)', $remainingPoint, $usedPoint);
        }

        $br = '<br/>';
        if ($transactionType == Type::CHECKOUT_ORDER_NEW) {
            $br = '';
        }

        $result = $this->_type->getTransactionDetail(
            $history->getTypeOfTransaction(),
            $history->getTransactionDetail(),
            $history->getStatus(),
            true
        );

        if ($miniResult != '') {
            $result = $result.$br.'<span style="font-size: 11px; color:#808080; font-weight: bold;">'.$miniResult.'</span>';
        }

        return $result;
    }
}

<?php


namespace MW\DailyDeal\Block\Adminhtml\Widget\Grid\Column;

abstract class AbstractCheckboxes extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * @var \MW\DailyDeal\Helper\Data
     */
    protected $dailyDealHelper;

    /**
     * @var \MW\DailyDeal\Model\DailydealFactory
     */
    protected $dailydealFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \MW\DailyDeal\Helper\Data $dailyDealHelper,
        \MW\DailyDeal\Model\DailydealFactory $dailydealFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dailyDealHelper = $dailyDealHelper;
        $this->dailydealFactory = $dailydealFactory;

        $this->_filterTypes['checkbox'] = 'MW\DailyDeal\Block\Adminhtml\Widget\Grid\Column\Filter\Checkbox';
    }

    /**
     * values.
     *
     * @return mixed
     */
    public function getValues()
    {
        if (!$this->hasData('values')) {
            $this->setData('values', $this->getSelectedValues());
        }

        return $this->getData('values');
    }

    /**
     * get selected rows.
     *
     * @return array
     */
    abstract public function getSelectedValues();
}

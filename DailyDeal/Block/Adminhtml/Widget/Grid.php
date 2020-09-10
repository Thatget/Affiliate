<?php


namespace MW\DailyDeal\Block\Adminhtml\Widget;

use MW\DailyDeal\Block\Adminhtml\Widget\Grid\Column\Filter\Checkbox as FilterCheckbox;

class Grid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * @var \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter
     */
    protected $converter;

    /**
     * @var \MW\DailyDeal\Helper\Data
     */
    protected $dailyDealHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data            $backendHelper
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \MW\DailyDeal\Helper\Data $dailyDealHelper,
        \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter $converter,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->dailyDealHelper = $dailyDealHelper;
        $this->converter = $converter;

        if ($this->hasData('serialize_grid') && count($this->getSelectedRows())) {
            $this->setDefaultFilter(
                ['checkbox_id' => FilterCheckbox::CHECKBOX_YES]
            );
        }
    }

    /**
     * get selected row values.
     *
     * @return array
     */
    public function getSelectedRows()
    {
        $selectedValues = $this->converter->toFlatArray(
            $this->dailyDealHelper->getTreeSelectedValues()
        );

        return array_values($selectedValues);
    }
}

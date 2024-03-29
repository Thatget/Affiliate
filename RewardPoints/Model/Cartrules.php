<?php

namespace MW\RewardPoints\Model;

class Cartrules extends \Magento\Rule\Model\AbstractModel
{
    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\CombineFactory
     */
    protected $_salesRuleCombineFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory
     */
    protected $_salesRuleProductCombineFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\SalesRule\Model\Rule\Condition\CombineFactory $salesRuleCombineFactory
     * @param \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $salesRuleProductCombineFactory
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
    	\Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
    	\Magento\SalesRule\Model\Rule\Condition\CombineFactory $salesRuleCombineFactory,
    	\Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $salesRuleProductCombineFactory,
    	\Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
    	\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
    	parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    	$this->_salesRuleCombineFactory = $salesRuleCombineFactory;
    	$this->_salesRuleProductCombineFactory = $salesRuleProductCombineFactory;
    }

	/**
     * Define resource model
     *
     * @return void
     */
	protected function _construct()
	{
		$this->_init('MW\RewardPoints\Model\ResourceModel\Cartrules');
        $this->setIdFieldName('rule_id');
	}

	/**
	 * Get conditions
	 *
	 * @return \Magento\SalesRule\Model\Rule\Condition\Combine
	 */
    public function getConditionsInstance()
    {
        return $this->_salesRuleCombineFactory->create();
    }

    /**
     * Get actions
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    public function getActionsInstance()
    {
        return $this->_salesRuleProductCombineFactory->create();
    }

    /**
     * Returns rule as an array for admin interface
     *
     * Output example:
     * array(
     *   'name'=>'Example rule',
     *   'conditions'=>{condition_combine::asArray}
     *   'actions'=>{action_collection::asArray}
     * )
     *
     * @return array
     */
    public function asArray(array $arrAttributes = [])
    {
        $out = [
            'name'        => $this->getName(),
            'start_at'    => $this->getStartAt(),
            'expire_at'   => $this->getExpireAt(),
            'description' => $this->getDescription(),
            'conditions'  => $this->getConditions()->asArray(),
            'actions'     => $this->getActions()->asArray(),
        ];

        return $out;
    }

    public function afterLoad()
    {
        $this->_afterLoad();
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
    }

    /**
     * Prepare data before saving
     */
    public function beforeSave()
    {
        // Serialize conditions
        if ($this->getConditions()) {
            $this->setConditionsSerialized(serialize($this->getConditions()->asArray()));
            $this->unsConditions();
        }

        // Serialize actions
        if ($this->getActions()) {
            $this->setActionsSerialized(serialize($this->getActions()->asArray()));
            $this->unsActions();
        }

        /**
         * Prepare customer group Ids if applicable and if they were set as string in comma separated format.
         * Backwards compatibility.
         */
        if ($this->hasCustomerGroupIds()) {
            $groupIds = $this->getCustomerGroupIds();
            if (is_array($groupIds) && !empty($groupIds)) {
                $this->setCustomerGroupIds(implode(',', $groupIds));
            }
        }

        if (!$this->getId()) {
            $this->isObjectNew(true);
        }
        $this->_eventManager->dispatch('model_save_before', ['object' => $this]);
        $this->_eventManager->dispatch($this->_eventPrefix . '_save_before', $this->_getEventData());

        return $this;
    }
    /**
     * Retrieve rule combine conditions model
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditions()
    {
        if (empty($this->_conditions)) {
            $this->_resetConditions();
        }
        // Load rule conditions if it is applicable
        if ($this->hasConditionsSerialized()) {
            $conditions = $this->getConditionsSerialized();
            if (!empty($conditions)) {
                $conditions = unserialize($conditions);
                if (is_array($conditions) && !empty($conditions)) {
                    $this->_conditions->loadArray($conditions);
                }
            }
            $this->unsConditionsSerialized();
        }

        return $this->_conditions;
    }

    /**
     * Initialize rule model data from array
     *
     * @param array $data
     * @return $this
     */
    public function loadPost(array $data)
    {
        $arr = $this->_convertFlatToRecursive($data);
        if (isset($arr['conditions'])) {
            $this->getConditions()->setConditions([])->loadArray($arr['conditions'][1]);
        }
        if (isset($arr['actions'])) {
            $this->getActions()->setActions([])->loadArray($arr['actions'][1], 'actions');
        }

        return $this;
    }

    public function getActions()
    {
        if (!$this->_actions) {
            $this->_resetActions();
        }

        // Load rule actions if it is applicable
        if ($this->hasActionsSerialized()) {
            $actions = $this->getActionsSerialized();
            if (!empty($actions)) {
                $actions = unserialize($actions);
                if (is_array($actions) && !empty($actions)) {
                    $this->_actions->loadArray($actions);
                }
            }
            $this->unsActionsSerialized();
        }

        return $this->_actions;
    }
}

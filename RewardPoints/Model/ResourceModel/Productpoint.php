<?php

namespace MW\RewardPoints\Model\ResourceModel;

class Productpoint extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	/**
     * Define main table
     *
     * @return void
     */
	protected function _construct()
	{
		$this->_init('mw_reward_product_point', 'id');
	}

	/**
	 *
	 */
	public function updateTable($data){
		$table = $data['table'];
		$bin_data = $data['values'];
		$where = $data['condition'];
		$insert = $data['insert'];
		$delete = $data['delete'];
		try{
			$connection = $this->_getConnection('read');
			$connection->beginTransaction();
			/* update item_id that really exist in table */
			if($bin_data) {
				$connection->update($table, $bin_data, $where);
			}
			/* insert history collect qty */
			if($insert) {
				$connection->insertMultiple($table, $insert);
			}
			/* delete */
			if($delete){
				$whereDelete = $connection->quoteInto('id in (?)', $delete);
				$connection->delete($table,$whereDelete);
			}

			$connection->commit();
			return true;
		}catch (Exception $e){
			$connection->rollBack();
		}
	}
}

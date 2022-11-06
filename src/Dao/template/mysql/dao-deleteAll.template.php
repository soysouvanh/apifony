<?php 
/**
 * Inputs:
 *	- $data['tableName']
 */
?>

	
	/**
	 * (non-PHPdoc)
	 * @see AbstractDao::deleteAll()
	 */
	public function deleteAll(): void
	{
		if(!$this->dataSource->query('DELETE FROM <?= $data['tableName']?>')) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
	}
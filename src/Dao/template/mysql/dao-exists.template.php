<?php 
/**
 * Inputs:
 *	- $data['tableName']
 *	- $data['primaryKeys']
 *	- $data['columnTypes']
 */

if(!empty($data['primaryKeys'])) {
	$whereNames = [];
	$bindTypes = '';
	$bindValues = [];
	foreach($data['primaryKeys'] as &$keys) {
		foreach($keys as &$fieldName) {
			$whereNames[$fieldName] = $fieldName . ' = ?';
			$bindTypes .= $data['columnTypes'][$fieldName]['bindType'];
			$bindValues[$fieldName] = '$data[\'' . $fieldName . '\']';
		}
	}
}
?>

	
	/**
	 * (non-PHPdoc)
	 * @see AbstractDao::exists()
	 */
	public function exists(array &$data): bool
	{
<?php if(empty($data['primaryKeys'])) {?>
		throw new \App\Exception\MethodNotAvailableException(__METHOD__ . ' not available.');
<?php } else {?>
		if(!($stmt = $this->dataSource->prepare('SELECT 1 FROM <?= $data['tableName']?> WHERE <?= implode(' AND ', $whereNames)?>'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('<?= $bindTypes?>', <?= implode(', ', $bindValues)?>)) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		if(!$stmt->bind_result($exists)) {
			$this->_throwSqlException($stmt, self::BIND_RESULT_STATEMENT);
		}
		
		$stmt->fetch();
		
		$stmt->close();
		
		return $exists === 1;
<?php }?>
	}
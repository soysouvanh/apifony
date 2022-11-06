<?php 
/**
 * Inputs:
 *	- $data['tableName']
 *	- $data['primaryKeys']
 *	- $data['columnTypes']
 */

if(!empty($data['primaryKeys'])) {
	$keyTypes = [];
	foreach($data['primaryKeys'] as &$keys) {
		foreach($keys as &$key) {
			$keyTypes[$key] = $data['columnTypes'][$key];
			unset($data['columnTypes'][$key]);
		}
	}
	
	$fieldNames = [];
	$bindTypes = '';
	$bindValues = [];
	foreach($data['columnTypes'] as $fieldName => &$typeData) {
		if($fieldName != 'created') {
			$fieldNames[$fieldName] = $fieldName . ' = ?';
			$bindTypes .= $typeData['bindType'];
			$bindValues[$fieldName] = '$data[\'' . $fieldName . '\']';
		}
	}
	
	$whereNames = [];
	foreach($keyTypes as $fieldName => &$typeData) {
		$whereNames[$fieldName] = $fieldName . ' = ?';
		$bindTypes .= $typeData['bindType'];
		$bindValues[$fieldName] = '$data[\'' . $fieldName . '\']';
	}
}
?>

	
	/**
	 * (non-PHPdoc)
	 * @see AbstractDao::update()
	 */
	public function update(array &$data): void
	{
<?php if(empty($data['primaryKeys'])) {?>
		throw new \App\Exception\MethodNotAvailableException(__METHOD__ . ' not available.');
<?php } else {?>
<?php if(isset($data['columnTypes']['updated'])) {?>
		$data['updated'] = date('Y-m-d H:i:s');
<?php }?>
<?php if(isset($data['columnTypes']['updatedDate'])) {?>
		$data['updatedDate'] = date('Y-m-d');
<?php }?>
<?php if(isset($data['columnTypes']['updatedTime'])) {?>
		$data['updatedTime'] = date('H:i:s');
<?php }?>
		if(!($stmt = $this->dataSource->prepare('UPDATE <?= $data['tableName']?> SET <?= implode(', ', $fieldNames)?> WHERE <?= implode(' AND ', $whereNames)?>'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('<?= $bindTypes?>', <?= implode(', ', $bindValues)?>)) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		
		$stmt->close();
<?php }?>
	}
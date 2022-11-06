<?php 
/**
 * Inputs:
 *	- $data['tableName']
 *	- $data['primaryKeys']
 *	- $data['uniqueKeys']
 *	- $data['indexKeys']
 *	- $data['otherKeys']
 *	- $data['columnTypes']
 */

if(!empty($data['primaryKeys'])) {
	$pkWhereNames = [];
	$pkBindTypes = '';
	$bindValues = ['columnName' => null];
	
	$hasUpdated = isset($data['columnTypes']['updated']);
	$hasUpdatedDate = isset($data['columnTypes']['updatedDate']);
	$hasUpdatedTime = isset($data['columnTypes']['updatedTime']);
	
	if($hasUpdated) {
		$bindValues['updated'] = '$data[\'updated\']';
	}
	if($hasUpdatedDate) {
		$bindValues['updatedDate'] = '$data[\'updatedDate\']';
	}
	if($hasUpdatedTime) {
		$bindValues['updatedTime'] = '$data[\'updatedTime\']';
	}

	foreach($data['primaryKeys']['PRIMARY'] as $key => &$fieldName) {
		$pkWhereNames[$fieldName] = $fieldName . ' = ?';
		$pkBindTypes .= $data['columnTypes'][$fieldName]['bindType'];
		$bindValues[$fieldName] = '$data[\'' . $fieldName . '\']';
	}
	
	foreach($data['columnTypes'] as $columName => &$columnData) {
		if(!isset($data['primaryKeys']['PRIMARY'][$columName])) {
			$setUpdated = $columName != 'updated' && $hasUpdated;
			$setUpdatedDate = $columName != 'updatedDate' && $columName != 'updatedTime' && $hasUpdatedDate;
			$setUpdatedTime = $columName != 'updatedDate' && $columName != 'updatedTime' && $hasUpdatedTime;

			$pks = implode(', ', $data['primaryKeys']['PRIMARY']);
			$bindValues['columnName'] = '$data[\'' . $columName . '\']';
			
			if($setUpdated) {
				$data['columnTypes'][$columName]['bindType'] .= 's';
			}
			if($setUpdatedDate) {
				$data['columnTypes'][$columName]['bindType'] .= 's';
			}
			if($setUpdatedTime) {
				$data['columnTypes'][$columName]['bindType'] .= 's';
			}
?>

	
	/**
	 * Update <?= $columName?> column by primary key (<?= $pks?>).
	 * @param array $data Associative array containing primary key (<?= $pks?>) and the column name (<?= $columName?>) to update.
	 * @return void.
	 * @throws \App\Exception\SqlException
	 */
	public function update<?= str_replace(' ', '', ucwords(str_replace('_', ' ', $columName)))?>(array &$data): void
	{
<?php if($setUpdated) {?>
		$data['updated'] = date('Y-m-d H:i:s');
<?php }?>
<?php if($setUpdatedDate) {?>
		$data['updatedDate'] = date('Y-m-d');
<?php }?>
<?php if($setUpdatedTime) {?>
		$data['updatedTime'] = date('H:i:s');
<?php }?>
		if(!($stmt = $this->dataSource->prepare('UPDATE <?= $data['tableName']?> SET <?= $columName?> = ?<?= $setUpdated ? ', updated = ?' : ''?><?= $setUpdatedDate ? ', updatedDate = ?' : ''?><?= $setUpdatedTime ? ', updatedTime = ?' : ''?> WHERE <?= implode(' AND ', $pkWhereNames)?>'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('<?= $data['columnTypes'][$columName]['bindType'] . $pkBindTypes?>', <?= implode(', ', $bindValues)?>)) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		
		$stmt->close();
	}<?php 
		}
	}
}
?>
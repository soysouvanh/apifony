<?php 
/**
 * Inputs:
 *	- $data['tableName']
 *	- $data['primaryKeys']
 *	- $data['columnTypes']
 */

$fieldNames = [];
$bindTypes = '';
$bindValues = [];
foreach($data['columnTypes'] as $fieldName => &$typeData) {
	$fieldNames[$fieldName] = $fieldName;
	$bindTypes .= $typeData['bindType'];
	$bindValues[$fieldName] = '$data[\'' . $fieldName . '\']';
}
?>

	
	/**
	 * (non-PHPdoc)
	 * @see AbstractDao::insert()
	 */
	public function insert(array &$data): void
	{
<?php 
	if(isset($data['primaryKeys']['PRIMARY']) && count($data['primaryKeys']['PRIMARY']) === 1) {
	$primaryKey = reset($data['primaryKeys']['PRIMARY']);
?>
		//Create identifier
		if(empty($data['<?= $primaryKey?>'])) {
			$data['<?= $primaryKey?>'] = $this->getNewId();
		}
		
<?php }?>
<?php if(isset($fieldNames['created'])) {?>
		$data['created'] = date('Y-m-d H:i:s');
<?php }?>
<?php if(isset($fieldNames['updated'])) {?>
		$data['updated'] = <?= isset($fieldNames['created']) ? '&$data[\'created\']' : 'date(\'Y-m-d H:i:s\')'?>;
<?php }?>
<?php if(isset($fieldNames['createdDate'])) {?>
		$data['createdDate'] = date('Y-m-d');
<?php }?>
<?php if(isset($fieldNames['createdTime'])) {?>
		$data['createdTime'] = date('H:i:s');
<?php }?>
<?php if(isset($fieldNames['updatedDate'])) {?>
		$data['updatedDate'] = <?= isset($fieldNames['createdDate']) ? '&$data[\'createdDate\']' : 'date(\'Y-m-d\')'?>;
<?php }?>
<?php if(isset($fieldNames['updatedTime'])) {?>
		$data['updatedTime'] = <?= isset($fieldNames['createdTime']) ? '&$data[\'createdTime\']' : 'date(\'H:i:s\')'?>;
<?php }?>
		if(!($stmt = $this->dataSource->prepare('INSERT INTO <?= $data['tableName']?>(<?= implode(', ', $fieldNames)?>) VALUES(<?= implode(', ', array_fill(0, count($fieldNames), '?'))?>)'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('<?= $bindTypes?>', <?= implode(', ', $bindValues)?>)) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		
		$stmt->close();
	}
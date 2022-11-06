<?php 
/**
 * Inputs:
 *	- $data['tableName']
 *	- $data['indexKeys']
 *	- $data['columnTypes']
 */

if(!empty($data['indexKeys'])) {
	$fieldNames = [];
	$recordData = [];
	foreach($data['columnTypes'] as $fieldName => &$typeData) {
		$fieldNames[$fieldName] = $fieldName;
		$recordData[$fieldName] = "\n\t\t\t\t'" . $fieldName . '\' => $__' . $fieldName;
	}
	
	foreach($data['indexKeys'] as &$indexKeys) {
		$methodSuffix = str_replace(' ', '', ucwords(str_replace('_', ' ', implode(' ', $indexKeys))));
		
		$arguments =[];
		$ands = [];
		$bindTypes = '';
		$bindNames = [];
		foreach($indexKeys as &$indexKey) {
			$arguments[] = self::PARAMETER_TYPES[$data['columnTypes'][$indexKey]['bindType']] .' $' .$indexKey;
			$ands[] = $indexKey . ' = ?';
			$bindTypes .= $data['columnTypes'][$indexKey]['bindType'];
			$bindNames[] = '$' . $indexKey;
		}
		
		$comment = implode('" ,"', $indexKeys);
		$arguments =implode(', ', $arguments);
		$ands = implode(' AND ', $ands);
		$bindNames = implode(', ', $bindNames);
?>

	
	/**
	 * Return list by index key "<?= $comment?>".
<?php foreach($indexKeys as &$indexKey) {?>
	 * @param <?= self::PARAMETER_TYPES[$data['columnTypes'][$indexKey]['bindType']]?> $<?= $indexKey?>.
<?php }?>
	 * @return array
	 * @throws \App\Exception\SqlException
	 */
	public function getListBy<?= $methodSuffix?>(<?= $arguments?>): array
	{
		if(!($stmt = $this->dataSource->prepare('SELECT <?= implode(', ', $fieldNames)?> FROM <?= $data['tableName']?> WHERE <?= $ands?>'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('<?= $bindTypes?>', <?= $bindNames?>)) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		if(!$stmt->bind_result($__<?= implode(', $__', $fieldNames)?>)) {
			$this->_throwSqlException($stmt, self::BIND_RESULT_STATEMENT);
		}
		
		$rows = [];
		while($stmt->fetch()) {
			$rows[] = [<?= implode(',', $recordData)?>

			];
		}
		$stmt->close();
		
		return $rows;
	}
	
	/**
	 * Determine existence by index key "<?= $comment?>".
<?php foreach($indexKeys as &$indexKey) {?>
	 * @param <?= self::PARAMETER_TYPES[$data['columnTypes'][$indexKey]['bindType']]?> $<?= $indexKey?>.
<?php }?>
	 * @return bool
	 * @throws \App\Exception\SqlException
	 */
	public function existsBy<?= $methodSuffix?>(<?= $arguments?>): bool {
		if(!($stmt = $this->dataSource->prepare('SELECT 1 FROM <?= $data['tableName']?> WHERE <?= $ands?>'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('<?= $bindTypes?>', <?= $bindNames?>)) {
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
	}<?php 
	}
}
?>
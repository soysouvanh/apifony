<?php 
/**
 * Inputs:
 *	- $data['tableName']
 *	- $data['uniqueKeys']
 *	- $data['columnTypes']
 */

if(!empty($data['uniqueKeys'])) {
	$fieldNames = [];
	$recordData = [];
	foreach($data['columnTypes'] as $fieldName => &$typeData) {
		$fieldNames[$fieldName] = $fieldName;
		$recordData[$fieldName] = "\n\t\t\t\t'" . $fieldName . '\' => $__' . $fieldName;
	}
	
	foreach($data['uniqueKeys'] as &$uniqueKeys) {
		$methodSuffix = str_replace(' ', '', ucwords(str_replace('_', ' ', implode(' ', $uniqueKeys))));
		
		$arguments =[];
		$ands = [];
		$bindTypes = '';
		$bindNames = [];
		foreach($uniqueKeys as &$uniqueKey) {
			$arguments[] = self::PARAMETER_TYPES[$data['columnTypes'][$uniqueKey]['bindType']] .' $' .$uniqueKey;
			$ands[] = $uniqueKey . ' = ?';
			$bindTypes .= $data['columnTypes'][$uniqueKey]['bindType'];
			$bindNames[] = '$' . $uniqueKey;
		}
		
		$comment = implode('" ,"', $uniqueKeys);
		$arguments = implode(', ', $arguments);
		$ands = implode(' AND ', $ands);
		$bindNames = implode(', ', $bindNames);
?>

	
	/**
	 * Return data by unique key "<?= $comment?>".
<?php foreach($uniqueKeys as &$uniqueKey) {?>
	 * @param <?= self::PARAMETER_TYPES[$data['columnTypes'][$uniqueKey]['bindType']]?> $<?= $uniqueKey?>.
<?php }?>
	 * @return array Array if found, otherwise null.
	 * @throws \App\Exception\SqlException
	 */
	public function getBy<?= $methodSuffix?>(<?= $arguments?>): ?array
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
		
		$stmt->fetch();
		
		$stmt->close();
		
		if(!empty($__<?= reset($fieldNames)?>)) {
			return [<?= implode(',', $recordData), "\n\t\t\t"?>];
		}
		
		return null;
	}
	
	/**
	 * Determine existence by unique key "<?= $comment?>".
<?php foreach($uniqueKeys as &$uniqueKey) {?>
	 * @param <?= self::PARAMETER_TYPES[$data['columnTypes'][$uniqueKey]['bindType']]?> $<?= $uniqueKey?>.
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
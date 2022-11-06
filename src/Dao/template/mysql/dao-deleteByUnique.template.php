<?php 
/**
 * Inputs:
 *	- $data['tableName']
 *	- $data['uniqueKeys']
 *	- $data['columnTypes']
 */

if(!empty($data['uniqueKeys'])) {
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
	 * Delete by unique key "<?= $comment?>".
<?php foreach($uniqueKeys as &$uniqueKey) {?>
	 * @param <?= self::PARAMETER_TYPES[$data['columnTypes'][$uniqueKey]['bindType']]?> $<?= $uniqueKey?>.
<?php }?>
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	public function deleteBy<?= $methodSuffix?>(<?= $arguments?>): void
	{
		if(!($stmt = $this->dataSource->prepare('DELETE FROM <?= $data['tableName']?> WHERE <?= $ands?>'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('<?= $bindTypes?>', <?= $bindNames?>)) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		
		$stmt->close();
	}<?php 
	}
}
?>
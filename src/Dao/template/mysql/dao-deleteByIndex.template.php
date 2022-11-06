<?php 
/**
 * Inputs:
 *	- $data['tableName']
 *	- $data['indexKeys']
 *	- $data['columnTypes']
 */

if(!empty($data['indexKeys'])) {
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
	 * Delete by index key "<?= $comment?>".
<?php foreach($indexKeys as &$indexKey) {?>
	 * @param <?= self::PARAMETER_TYPES[$data['columnTypes'][$indexKey]['bindType']]?> $<?= $indexKey?>.
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
	}
	<?php 
	}
}
?>
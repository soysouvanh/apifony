<?php 
/**
 * Inputs:
 *	- $data['tableName']
 *	- $data['primaryKeys']
 *	- $data['columnTypes']
 */

$fieldNames = array_keys($data['columnTypes']);
?>

	
	/**
	 * (non-PHPdoc)
	 * @see AbstractDao::getAll()
	 */
	public function getAll(array $fieldNames = null, int $page = 1, int $limit = 10, string $orderClause = null): array
	{
		$page = $page > 0 ? $page - 1 : 0;
		if($limit < 1) {
			$limit = 10;
		}
		
		$fieldNames = !$fieldNames&&count($fieldNames) ? implode(',', $fieldNames) : '*';
		$sql = 'SELECT <?= implode(', ', $fieldNames)?> FROM <?= $data['tableName']?> WHERE 1 LIMIT ' . ($page * $limit) . ', ' . $limit;
		if(!empty($orderClause)) {
			$sql .= ' ORDER BY ' . $orderClause;
		}
		
		if(!($result = $this->dataSource->query($sql))) {
			throw new SqlException($this->dataSource->error);
		}
		
		$rows = [];
		while($row = $result->fetch_assoc()) {
			$rows[] = row;
		}
		
		$result->close();
		
		return $rows;
	}
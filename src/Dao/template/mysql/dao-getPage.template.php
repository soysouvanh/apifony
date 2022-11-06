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

$fieldNames = [];
foreach($data['columnTypes'] as $fieldName => &$typeData) {
	$fieldNames[$fieldName] = $fieldName;
}
?>

	
	/**
	 * (non-PHPdoc)
	 * @see AbstractDao::getPage()
	 */
	public function getPage(array $filters = null, int $page = 1, int $limit = 10, bool $count = true, string $orderClause = null): array
	{
<?php if(empty($data['primaryKeys']) && empty($data['uniqueKeys']) && empty($data['indexKeys'])) {?>
		throw new \App\Exception\MethodNotAvailableException(__METHOD__);
<?php 
	}
	
	else {
		$distinctClause = [];
		$filters = isset($data['primaryKeys']['PRIMARY']) ? $data['primaryKeys']['PRIMARY'] : [];
		foreach($data['uniqueKeys'] as $array) {
			$filters = array_merge($filters, $array);
			$distinctClause = array_merge($distinctClause, $array);
		}
		foreach($data['indexKeys'] as $array) {
			$filters = array_merge($filters, $array);
		}
		$filters = array_merge($filters, $data['otherKeys']);
		
		if(!empty($data['primaryKeys'])) {
			$distinctClause = $data['primaryKeys']['PRIMARY'];
		}
		elseif(empty($distinctClause)) {
			$distinctClause = $fieldNames;
		}
		$distinctClause = implode(', ', $distinctClause);
?>
		//Count number of records
		$from = '<?= $data['tableName']?>';
		$where = '1';
		$referenceValues = [0 => ''];
		
		//Case filters exist
		if(!empty($filters)) {
			//Build SQL with operators
			if(is_array(reset($filters))) {
				$filters = $this->getFiltersClause($filters);
				$where = $filters['where'];
				$referenceValues = &$filters['referenceValues'];
			}
			
			//Build SQL by default on index columns
			else {
				//Build SQL
<?php 
foreach($filters as &$key) {
?>
				if(isset($filters['<?= $key?>'])) {
					$where .= ' AND <?= $key?> = ?';
					$referenceValues[0] .= '<?= $data['columnTypes'][$key]['bindType']?>';
					$referenceValues[] = &$filters['<?= $key?>'];
				}

<?php 
}

if(isset($data['columnTypes']['created'])) {
?>
				if(isset($filters['createdStart']) || isset($filters['createdEnd'])) {
					if(isset($filters['createdStart']) && isset($filters['createdEnd'])) {
						$where .= ' AND created BETWEEN ? AND ?';
						$referenceValues[0] .= 'ss';
						$referenceValues[] = &$filters['createdStart'];
						$referenceValues[] = &$filters['createdEnd'];
					}
					elseif(isset($filters['createdStart'])) {
						$where .= ' AND created >= ?';
						$referenceValues[0] .= 's';
						$referenceValues[] = &$filters['createdStart'];
					}
					else {
						$where .= ' AND created <= ?';
						$referenceValues[0] .= 's';
						$referenceValues[] = &$filters['createdEnd'];
					}
				}

<?php 
}

if(isset($data['columnTypes']['createdDate'])) {
?>
				if(isset($filters['createdDateStart']) || isset($filters['createdDateEnd'])) {
					if(isset($filters['createdDateStart']) && isset($filters['createdDateEnd'])) {
						$where .= ' AND createdDate BETWEEN ? AND ?';
						$referenceValues[0] .= 'ss';
						$referenceValues[] = &$filters['createdDateStart'];
						$referenceValues[] = &$filters['createdDateEnd'];
					}
					elseif(isset($filters['createdDateStart'])) {
						$where .= ' AND createdDate >= ?';
						$referenceValues[0] .= 's';
						$referenceValues[] = &$filters['createdDateStart'];
					}
					else {
						$where .= ' AND createdDate <= ?';
						$referenceValues[0] .= 's';
						$referenceValues[] = &$filters['createdDateEnd'];
					}
				}

<?php 
}

if(isset($data['columnTypes']['createdTime'])) {
?>
				if(isset($filters['createdTimeStart']) || isset($filters['createdTimeEnd'])) {
					if(isset($filters['createdTimeStart']) && isset($filters['createdTimeEnd'])) {
						$where .= ' AND createdTime BETWEEN ? AND ?';
						$referenceValues[0] .= 'ss';
						$referenceValues[] = &$filters['createdTimeStart'];
						$referenceValues[] = &$filters['createdTimeEnd'];
					}
					elseif(isset($filters['createdTimeStart'])) {
						$where .= ' AND createdTime >= ?';
						$referenceValues[0] .= 's';
						$referenceValues[] = &$filters['createdTimeStart'];
					}
					else {
						$where .= ' AND createdTime <= ?';
						$referenceValues[0] .= 's';
						$referenceValues[] = &$filters['createdTimeEnd'];
					}
				}

<?php 
}

if(isset($data['columnTypes']['updated'])) {
?>
				if(isset($filters['updatedStart']) || isset($filters['updatedEnd'])) {
					if(isset($filters['updatedStart']) && isset($filters['updatedEnd'])) {
						$where .= ' AND updated BETWEEN ? AND ?';
						$referenceValues[0] .= 'ss';
						$referenceValues[] = &$filters['updatedStart'];
						$referenceValues[] = &$filters['updatedEnd'];
					}
					elseif(isset($filters['updatedStart'])) {
						$where .= ' AND updated >= ?';
						$referenceValues[0] .= 's';
						$referenceValues[] = &$filters['updatedStart'];
					}
					else {
						$where .= ' AND updated <= ?';
						$referenceValues[0] .= 's';
						$referenceValues[] = &$filters['updatedEnd'];
					}
				}
			
<?php 
}
?>
			}
		}
		
		if($count) {
			if($referenceValues[0] !== '') {
				if(!($stmt = $this->dataSource->prepare('SELECT COUNT(DISTINCT <?= $distinctClause?>) FROM ' . $from . ' WHERE ' . $where))) {
					throw new \App\Exception\SqlException($this->dataSource->error);
				}
				if(call_user_func_array([$stmt, 'bind_param'], $referenceValues) === false) {
					$this->_throwSqlException($stmt);
				}
				if(!$stmt->execute()) {
					$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
				}
				if(!$stmt->bind_result($n)) {
					$this->_throwSqlException($stmt, self::BIND_RESULT_STATEMENT);
				}
				$stmt->fetch();
				$stmt->close();
				$n = (int)$n;
			}
			else {
				if(!($result = $this->dataSource->query('SELECT COUNT(DISTINCT <?= $distinctClause?>) FROM <?= $data['tableName']?>'))) {
					throw new \App\Exception\SqlException($this->dataSource->error);
				}
				$n = $result->fetch_row();
				$result->close();
				$n = (int)$n[0];
			}
		}
		else $n = null;
		
		//Retrieve data
		$rows = [];
		$index = ($page-1) * $limit;
		if(!empty($orderClause)) {
			$orderClause = ' ORDER BY ' . $orderClause;
		}
		if(!($stmt = $this->dataSource->prepare('SELECT DISTINCT <?= implode(', ', $fieldNames)?> FROM ' . $from . ' WHERE ' . $where . $orderClause . ' LIMIT ?, ?'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		$referenceValues[0] .= 'ii';
		$referenceValues[] = &$index;
		$referenceValues[] = &$limit;
		if(call_user_func_array([$stmt, 'bind_param'], $referenceValues) === false) {
			$this->_throwSqlException($stmt);
		}
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		if(!$stmt->bind_result($__<?= implode(', $__', $fieldNames)?>)) {
			$this->_throwSqlException($stmt, self::BIND_RESULT_STATEMENT);
		}
		while($stmt->fetch()) {
			$rows[] = [<?php 
	foreach($fieldNames as $key => &$value) {
		$value = "\n\t\t\t\t'" . $key . '\' => $__' . $key;
	}
	echo implode(',', $fieldNames);
?>

			];
		}
		$stmt->close();
		
		return $count ? ['results' => $rows, 'count' => $n] : $rows;
<?php }?>
	}
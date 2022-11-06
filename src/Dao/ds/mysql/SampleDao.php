<?php 
namespace App\Dao\ds\mysql;

/**
 * DAO class for "sample" table.
 * @author Apifony DAO generator by Vincent SOYSOUVANH.
 */
 class SampleDao extends \App\Dao\AbstractDao {
	/**
	 * Constructor.
	 * @param \App\Service\MysqlService $dataSource Data source.
	 * @return void
	 */
	public function __construct(\App\Service\MysqlService &$dataSource)
	{
		$this->dataSource = &$dataSource;
		$this->tableName = 'sample';
		$this->fields = [
			'sampleId' => self::INTEGER_FIELD_TYPE,
			'simpleIndex' => self::STRING_FIELD_TYPE,
			'index1on2' => self::INTEGER_FIELD_TYPE,
			'index2on2' => self::INTEGER_FIELD_TYPE,
			'label' => self::STRING_FIELD_TYPE,
			'description' => self::STRING_FIELD_TYPE,
			'createdDate' => self::STRING_FIELD_TYPE,
			'createdTime' => self::STRING_FIELD_TYPE
		];
		$this->primaryKey = ['sampleId'];
	}
	
	/**
	 * (non-PHPdoc)
	 * @see AbstractDao::insert()
	 */
	public function insert(array &$data): void
	{
		//Create identifier
		if(empty($data['sampleId'])) {
			$data['sampleId'] = $this->getNewId();
		}
		
		$data['createdDate'] = date('Y-m-d');
		$data['createdTime'] = date('H:i:s');
		if(!($stmt = $this->dataSource->prepare('INSERT INTO sample(sampleId, simpleIndex, index1on2, index2on2, label, description, createdDate, createdTime) VALUES(?, ?, ?, ?, ?, ?, ?, ?)'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('isiissss', $data['sampleId'], $data['simpleIndex'], $data['index1on2'], $data['index2on2'], $data['label'], $data['description'], $data['createdDate'], $data['createdTime'])) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		
		$stmt->close();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see AbstractDao::update()
	 */
	public function update(array &$data): void
	{
		if(!($stmt = $this->dataSource->prepare('UPDATE sample SET simpleIndex = ?, index1on2 = ?, index2on2 = ?, label = ?, description = ?, createdDate = ?, createdTime = ? WHERE sampleId = ?'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('siissssi', $data['simpleIndex'], $data['index1on2'], $data['index2on2'], $data['label'], $data['description'], $data['createdDate'], $data['createdTime'], $data['sampleId'])) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		
		$stmt->close();
	}
	
	/**
	 * Update simpleIndex column by primary key (sampleId).
	 * @param array $data Associative array containing primary key (sampleId) and the column name (simpleIndex) to update.
	 * @return void.
	 * @throws \App\Exception\SqlException
	 */
	public function updateSimpleIndex(array &$data): void
	{
		if(!($stmt = $this->dataSource->prepare('UPDATE sample SET simpleIndex = ? WHERE sampleId = ?'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('si', $data['simpleIndex'], $data['sampleId'])) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		
		$stmt->close();
	}
	
	/**
	 * Update index1on2 column by primary key (sampleId).
	 * @param array $data Associative array containing primary key (sampleId) and the column name (index1on2) to update.
	 * @return void.
	 * @throws \App\Exception\SqlException
	 */
	public function updateIndex1on2(array &$data): void
	{
		if(!($stmt = $this->dataSource->prepare('UPDATE sample SET index1on2 = ? WHERE sampleId = ?'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('ii', $data['index1on2'], $data['sampleId'])) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		
		$stmt->close();
	}
	
	/**
	 * Update index2on2 column by primary key (sampleId).
	 * @param array $data Associative array containing primary key (sampleId) and the column name (index2on2) to update.
	 * @return void.
	 * @throws \App\Exception\SqlException
	 */
	public function updateIndex2on2(array &$data): void
	{
		if(!($stmt = $this->dataSource->prepare('UPDATE sample SET index2on2 = ? WHERE sampleId = ?'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('ii', $data['index2on2'], $data['sampleId'])) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		
		$stmt->close();
	}
	
	/**
	 * Update label column by primary key (sampleId).
	 * @param array $data Associative array containing primary key (sampleId) and the column name (label) to update.
	 * @return void.
	 * @throws \App\Exception\SqlException
	 */
	public function updateLabel(array &$data): void
	{
		if(!($stmt = $this->dataSource->prepare('UPDATE sample SET label = ? WHERE sampleId = ?'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('si', $data['label'], $data['sampleId'])) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		
		$stmt->close();
	}
	
	/**
	 * Update description column by primary key (sampleId).
	 * @param array $data Associative array containing primary key (sampleId) and the column name (description) to update.
	 * @return void.
	 * @throws \App\Exception\SqlException
	 */
	public function updateDescription(array &$data): void
	{
		if(!($stmt = $this->dataSource->prepare('UPDATE sample SET description = ? WHERE sampleId = ?'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('si', $data['description'], $data['sampleId'])) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		
		$stmt->close();
	}
	
	/**
	 * Update createdDate column by primary key (sampleId).
	 * @param array $data Associative array containing primary key (sampleId) and the column name (createdDate) to update.
	 * @return void.
	 * @throws \App\Exception\SqlException
	 */
	public function updateCreatedDate(array &$data): void
	{
		if(!($stmt = $this->dataSource->prepare('UPDATE sample SET createdDate = ? WHERE sampleId = ?'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('si', $data['createdDate'], $data['sampleId'])) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		
		$stmt->close();
	}
	
	/**
	 * Update createdTime column by primary key (sampleId).
	 * @param array $data Associative array containing primary key (sampleId) and the column name (createdTime) to update.
	 * @return void.
	 * @throws \App\Exception\SqlException
	 */
	public function updateCreatedTime(array &$data): void
	{
		if(!($stmt = $this->dataSource->prepare('UPDATE sample SET createdTime = ? WHERE sampleId = ?'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('si', $data['createdTime'], $data['sampleId'])) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		
		$stmt->close();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see AbstractDao::exists()
	 */
	public function exists(array &$data): bool
	{
		if(!($stmt = $this->dataSource->prepare('SELECT 1 FROM sample WHERE sampleId = ?'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('i', $data['sampleId'])) {
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
	}
	
	/**
	 * (non-PHPdoc)
	 * @see AbstractDao::get()
	 */
	public function get(array &$data): ?array
	{
		if(!($stmt = $this->dataSource->prepare('SELECT sampleId, simpleIndex, index1on2, index2on2, label, description, createdDate, createdTime FROM sample WHERE sampleId = ?'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('i', $data['sampleId'])) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		if(!$stmt->bind_result($__sampleId, $__simpleIndex, $__index1on2, $__index2on2, $__label, $__description, $__createdDate, $__createdTime)) {
			$this->_throwSqlException($stmt, self::BIND_RESULT_STATEMENT);
		}
		
		$stmt->fetch();
		
		$stmt->close();
		
		if(!empty($__sampleId)) {
			return [
				'sampleId' => $__sampleId,
				'simpleIndex' => $__simpleIndex,
				'index1on2' => $__index1on2,
				'index2on2' => $__index2on2,
				'label' => $__label,
				'description' => $__description,
				'createdDate' => $__createdDate,
				'createdTime' => $__createdTime
			];
		}
		
		return null;
	}
	
	/**
	 * Return data by unique key "label".
	 * @param string $label.
	 * @return array Array if found, otherwise null.
	 * @throws \App\Exception\SqlException
	 */
	public function getByLabel(string $label): ?array
	{
		if(!($stmt = $this->dataSource->prepare('SELECT sampleId, simpleIndex, index1on2, index2on2, label, description, createdDate, createdTime FROM sample WHERE label = ?'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('s', $label)) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		if(!$stmt->bind_result($__sampleId, $__simpleIndex, $__index1on2, $__index2on2, $__label, $__description, $__createdDate, $__createdTime)) {
			$this->_throwSqlException($stmt, self::BIND_RESULT_STATEMENT);
		}
		
		$stmt->fetch();
		
		$stmt->close();
		
		if(!empty($__sampleId)) {
			return [
				'sampleId' => $__sampleId,
				'simpleIndex' => $__simpleIndex,
				'index1on2' => $__index1on2,
				'index2on2' => $__index2on2,
				'label' => $__label,
				'description' => $__description,
				'createdDate' => $__createdDate,
				'createdTime' => $__createdTime
			];
		}
		
		return null;
	}
	
	/**
	 * Determine existence by unique key "label".
	 * @param string $label.
	 * @return bool
	 * @throws \App\Exception\SqlException
	 */
	public function existsByLabel(string $label): bool {
		if(!($stmt = $this->dataSource->prepare('SELECT 1 FROM sample WHERE label = ?'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('s', $label)) {
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
	}
	
	/**
	 * Return list by index key "simpleIndex".
	 * @param string $simpleIndex.
	 * @return array
	 * @throws \App\Exception\SqlException
	 */
	public function getListBySimpleIndex(string $simpleIndex): array
	{
		if(!($stmt = $this->dataSource->prepare('SELECT sampleId, simpleIndex, index1on2, index2on2, label, description, createdDate, createdTime FROM sample WHERE simpleIndex = ?'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('s', $simpleIndex)) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		if(!$stmt->bind_result($__sampleId, $__simpleIndex, $__index1on2, $__index2on2, $__label, $__description, $__createdDate, $__createdTime)) {
			$this->_throwSqlException($stmt, self::BIND_RESULT_STATEMENT);
		}
		
		$rows = [];
		while($stmt->fetch()) {
			$rows[] = [
				'sampleId' => $__sampleId,
				'simpleIndex' => $__simpleIndex,
				'index1on2' => $__index1on2,
				'index2on2' => $__index2on2,
				'label' => $__label,
				'description' => $__description,
				'createdDate' => $__createdDate,
				'createdTime' => $__createdTime
			];
		}
		$stmt->close();
		
		return $rows;
	}
	
	/**
	 * Determine existence by index key "simpleIndex".
	 * @param string $simpleIndex.
	 * @return bool
	 * @throws \App\Exception\SqlException
	 */
	public function existsBySimpleIndex(string $simpleIndex): bool {
		if(!($stmt = $this->dataSource->prepare('SELECT 1 FROM sample WHERE simpleIndex = ?'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('s', $simpleIndex)) {
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
	}
	
	/**
	 * Return list by index key "index1on2" ,"index2on2".
	 * @param int $index1on2.
	 * @param int $index2on2.
	 * @return array
	 * @throws \App\Exception\SqlException
	 */
	public function getListByIndex1on2Index2on2(int $index1on2, int $index2on2): array
	{
		if(!($stmt = $this->dataSource->prepare('SELECT sampleId, simpleIndex, index1on2, index2on2, label, description, createdDate, createdTime FROM sample WHERE index1on2 = ? AND index2on2 = ?'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('ii', $index1on2, $index2on2)) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		if(!$stmt->bind_result($__sampleId, $__simpleIndex, $__index1on2, $__index2on2, $__label, $__description, $__createdDate, $__createdTime)) {
			$this->_throwSqlException($stmt, self::BIND_RESULT_STATEMENT);
		}
		
		$rows = [];
		while($stmt->fetch()) {
			$rows[] = [
				'sampleId' => $__sampleId,
				'simpleIndex' => $__simpleIndex,
				'index1on2' => $__index1on2,
				'index2on2' => $__index2on2,
				'label' => $__label,
				'description' => $__description,
				'createdDate' => $__createdDate,
				'createdTime' => $__createdTime
			];
		}
		$stmt->close();
		
		return $rows;
	}
	
	/**
	 * Determine existence by index key "index1on2" ,"index2on2".
	 * @param int $index1on2.
	 * @param int $index2on2.
	 * @return bool
	 * @throws \App\Exception\SqlException
	 */
	public function existsByIndex1on2Index2on2(int $index1on2, int $index2on2): bool {
		if(!($stmt = $this->dataSource->prepare('SELECT 1 FROM sample WHERE index1on2 = ? AND index2on2 = ?'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('ii', $index1on2, $index2on2)) {
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
	}
	
	/**
	 * (non-PHPdoc)
	 * @see AbstractDao::getPage()
	 */
	public function getPage(array $filters = null, int $page = 1, int $limit = 10, bool $count = true, string $orderClause = null): array
	{
		//Count number of records
		$from = 'sample';
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
				if(isset($filters['sampleId'])) {
					$where .= ' AND sampleId = ?';
					$referenceValues[0] .= 'i';
					$referenceValues[] = &$filters['sampleId'];
				}

				if(isset($filters['label'])) {
					$where .= ' AND label = ?';
					$referenceValues[0] .= 's';
					$referenceValues[] = &$filters['label'];
				}

				if(isset($filters['simpleIndex'])) {
					$where .= ' AND simpleIndex = ?';
					$referenceValues[0] .= 's';
					$referenceValues[] = &$filters['simpleIndex'];
				}

				if(isset($filters['index1on2'])) {
					$where .= ' AND index1on2 = ?';
					$referenceValues[0] .= 'i';
					$referenceValues[] = &$filters['index1on2'];
				}

				if(isset($filters['index2on2'])) {
					$where .= ' AND index2on2 = ?';
					$referenceValues[0] .= 'i';
					$referenceValues[] = &$filters['index2on2'];
				}

				if(isset($filters['description'])) {
					$where .= ' AND description = ?';
					$referenceValues[0] .= 's';
					$referenceValues[] = &$filters['description'];
				}

				if(isset($filters['createdDate'])) {
					$where .= ' AND createdDate = ?';
					$referenceValues[0] .= 's';
					$referenceValues[] = &$filters['createdDate'];
				}

				if(isset($filters['createdTime'])) {
					$where .= ' AND createdTime = ?';
					$referenceValues[0] .= 's';
					$referenceValues[] = &$filters['createdTime'];
				}

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

			}
		}
		
		if($count) {
			if($referenceValues[0] !== '') {
				if(!($stmt = $this->dataSource->prepare('SELECT COUNT(DISTINCT sampleId) FROM ' . $from . ' WHERE ' . $where))) {
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
				if(!($result = $this->dataSource->query('SELECT COUNT(DISTINCT sampleId) FROM sample'))) {
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
		if(!($stmt = $this->dataSource->prepare('SELECT DISTINCT sampleId, simpleIndex, index1on2, index2on2, label, description, createdDate, createdTime FROM ' . $from . ' WHERE ' . $where . $orderClause . ' LIMIT ?, ?'))) {
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
		if(!$stmt->bind_result($__sampleId, $__simpleIndex, $__index1on2, $__index2on2, $__label, $__description, $__createdDate, $__createdTime)) {
			$this->_throwSqlException($stmt, self::BIND_RESULT_STATEMENT);
		}
		while($stmt->fetch()) {
			$rows[] = [
				'sampleId' => $__sampleId,
				'simpleIndex' => $__simpleIndex,
				'index1on2' => $__index1on2,
				'index2on2' => $__index2on2,
				'label' => $__label,
				'description' => $__description,
				'createdDate' => $__createdDate,
				'createdTime' => $__createdTime
			];
		}
		$stmt->close();
		
		return $count ? ['results' => $rows, 'count' => $n] : $rows;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see AbstractDao::delete()
	 */
	public function delete(array &$data): void
	{
		if(!($stmt = $this->dataSource->prepare('DELETE FROM sample WHERE sampleId = ?'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('i', $data['sampleId'])) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		
		$stmt->close();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see AbstractDao::deleteAll()
	 */
	public function deleteAll(): void
	{
		if(!$this->dataSource->query('DELETE FROM sample')) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
	}
	
	/**
	 * Delete by unique key "label".
	 * @param string $label.
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	public function deleteByLabel(string $label): void
	{
		if(!($stmt = $this->dataSource->prepare('DELETE FROM sample WHERE label = ?'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('s', $label)) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		
		$stmt->close();
	}
	
	/**
	 * Delete by index key "simpleIndex".
	 * @param string $simpleIndex.
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	public function deleteBySimpleIndex(string $simpleIndex): void
	{
		if(!($stmt = $this->dataSource->prepare('DELETE FROM sample WHERE simpleIndex = ?'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('s', $simpleIndex)) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		
		$stmt->close();
	}
	
	
	/**
	 * Delete by index key "index1on2" ,"index2on2".
	 * @param int $index1on2.
	 * @param int $index2on2.
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	public function deleteByIndex1on2Index2on2(int $index1on2, int $index2on2): void
	{
		if(!($stmt = $this->dataSource->prepare('DELETE FROM sample WHERE index1on2 = ? AND index2on2 = ?'))) {
			throw new \App\Exception\SqlException($this->dataSource->error);
		}
		if(!$stmt->bind_param('ii', $index1on2, $index2on2)) {
			$this->_throwSqlException($stmt);
		}
		
		if(!$stmt->execute()) {
			$this->_throwSqlException($stmt, self::EXECUTE_STATEMENT);
		}
		
		$stmt->close();
	}
	
}
?>
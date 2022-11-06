<?php
namespace App\Dao;

/**
 * Abstract Dao class.
 */
abstract class AbstractDao
{
	/**
	 * Integer field type.
	 * @var int
	 */
	const INTEGER_FIELD_TYPE = 1;
	
	/**
	 * Big int field type.
	 * @var int
	 */
	const LONG_FIELD_TYPE = 2;
	
	/**
	 * Float field type.
	 * @var int
	 */
	const FLOAT_FIELD_TYPE = 3;
	
	/**
	 * String field type.
	 * @var int
	 */
	const STRING_FIELD_TYPE = 4;
	
	/**
	 * Boolean field type.
	 * @var int
	 */
	const BOOLEAN_FIELD_TYPE = 5;
	
	/**
	 * Date field type.
	 * @var int
	 */
	const DATE_FIELD_TYPE = 6;
	
	/**
	 * Bind parameters statement.
	 * @var string
	 */
	const BIND_PARAM_STATEMENT = 'bind_param';
	
	/**
	 * Execute statement.
	 * @var string
	 */
	const EXECUTE_STATEMENT = 'execute';
	
	/**
	 * Bind result statement.
	 * @var string
	 */
	const BIND_RESULT_STATEMENT = 'bind_result';
	
	/**
	 * Fetch array statement.
	 * @var string
	 */
	const FETCH_ARRAY_STATEMENT = 'fetch_array';
	
	/**
	 * Sort by ascending.
	 * @var string
	 */
	const ASCENDING_SORT = 'ASC';
	
	/**
	 * Sort by descending.
	 * @var string
	 */
	const DESCENDING_SORT = 'DESC';
	
	/**
	 * Data source.
	 * @var object
	 */
	protected object $dataSource;
	
	/**
	 * Table name.
	 * @var string
	 */
	protected $tableName = null;
	
	/**
	 * Table fields definition.
	 * @var array
	 */
	protected $fields = null;
	
	/**
	 * Primary key.
	 * @var array
	 */
	protected $primaryKey = null;
	
	/**
	 * Insert a record into the table.
	 * @param array $data Record data to insert.
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	abstract public function insert(array &$data): void;

	/**
	 * Update a table record.
	 * @param array $data Record data to update. $data must contain the primary key.
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	abstract public function update(array &$data): void;
	
	/**
	 * Delete a record from the table.
	 * @param array $data Primary key in associative array.
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	abstract public function delete(array &$data): void;

	/**
	 * Delete all records from the table
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	abstract public function deleteAll(): void;

	/**
	 * Determine existence by primary key or unique index.
	 * @param array $data Primary key in associative array.
	 * @return bool true if exists, otherwise false.
	 * @throws \App\Exception\SqlException
	 */
	abstract public function exists(array &$data): bool;

	/**
	 * Return data by primary key or unique index.
	 * @param array $data Primary key in associative array.
	 * @return array Array if found, otherwise null.
	 * @throws \App\Exception\SqlException
	 */
	abstract public function get(array &$data): ?array;

	/**
	 * Return page of records collection.
	 * @param array $filters (optional) Associative array. null by default.
	 * @param int $page (optional) Page number, beginning by 1.
	 * @param int $limit (optional) Number of records per page. If 0, then return all rows filtered by $filters. 10 by default.
	 * @param bool $count (optional) If true, return ['results' => <array>, 'count' => <int>], otherwise <array>.
	 * @param string $orderClause (optional) Order clause. null by default.
	 * @return array ['results' => <Page rows>, 'count' => <Total rows>] | <Page rows>
	 */
	abstract public function getPage(array $filters = null, int $page = 1, int $limit = 10, bool $count = true, string $orderClause = null): array;
	
	/**
	 * Throw Sql Exception.
	 * @param mysqli_stmt $stmt PDO statement.
	 * @param string (optional) Statement function name: self::BIND_PARAM_STATEMENT, self::EXECUTE_STATEMENT or self::BIND_RESULT_STATEMENT. self::BIND_PARAM_STATEMENT by default.
	 * @param string (optional) $message Supplementary message. Null by default.
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	protected function _throwSqlException(\mysqli_stmt &$stmt, string $stmtFunctionName = self::BIND_PARAM_STATEMENT, string $message = null): void
	{
		$msg = empty($stmt->error) ? $stmtFunctionName : $stmt->error;
		$cod = $stmt->errno;
		$stmt->close();
		throw new \App\Exception\SqlException($message ? $message . ' - ' . $msg : $msg, $cod);
	}

	/**
	 * Return a new identifier.
	 * @return int New identifier.
	 * @throws \App\Exception\SqlException
	 */
	public function getNewId(): int
	{
		if(!$this->primaryKey || count($this->primaryKey) !== 1 || ($this->fields[$this->primaryKey[0]] !== self::INTEGER_FIELD_TYPE && $this->fields[$this->primaryKey[0]] !== self::LONG_FIELD_TYPE)) {
			throw new SqlException('Primary key int type missing: ' . get_class($this));
		}
		$identifierName = &$this->primaryKey[0];
		if(!($result = $this->dataSource->query('SELECT MIN(' . $identifierName . '+1) AS ' . $identifierName . ' FROM ' . $this->tableName . ' WHERE ' . $identifierName . '+1 NOT IN (SELECT ' . $identifierName . ' FROM ' . $this->tableName . ')'))) {
			throw new SqlException($this->dataSource->error);
		}
		$row = $result->fetch_assoc();
		
		$result->close();
		
		return empty($row[$identifierName]) ? 1 : $row[$identifierName];
	}
}
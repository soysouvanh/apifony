<?php
namespace App\Dao\ds\pdo;

/**
 * Sample Dao class.
 */
class SampleDao extends \App\Dao\AbstractDao
{
	/**
	 * Constructor.
	 * @param object $ds Data source.
	 * @return void
	 */
	public function __construct(object &$ds)
	{
		$this->ds = &$ds;
		$this->tableName = 'sample';
		$this->fields = [
			'sampleId' => self::INTEGER_FIELD_TYPE,
			'simpleIndex' => self::STRING_FIELD_TYPE,
			'index1on2' => self::INTEGER_FIELD_TYPE,
			'index2on2' => self::INTEGER_FIELD_TYPE,
			'label' => self::STRING_FIELD_TYPE,
			'desciption' => self::STRING_FIELD_TYPE,
			'createDate' => self::STRING_FIELD_TYPE,
			'createdTime' => self::STRING_FIELD_TYPE
		];
		$this->primaryKey = [
			'sampleId'
		];
	}

	/**
	 * Insert a record into the table.
	 * @param array $data Record data to insert.
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	public function insert(array &$data): void
	{

	}

	/**
	 * Update a table record.
	 * @param array $data Record data to update. $data must contain the primary key.
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	public function update(array &$data): void
	{

	}
	
	/**
	 * Delete a record from the table.
	 * @param array $data Primary key in associative array.
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	public function delete(array &$data): void
	{

	}

	/**
	 * Delete all records from the table
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	public function deleteAll(): void
	{

	}

	/**
	 * Determine existence by primary key or unique index.
	 * @param array $data Primary key in associative array.
	 * @return bool true if exists, otherwise false.
	 * @throws \App\Exception\SqlException
	 */
	public function exists(array &$data): bool
	{

	}

	/**
	 * Return data by primary key or unique index.
	 * @param array $data Primary key in associative array.
	 * @return array Array if found, otherwise null.
	 * @throws \App\Exception\SqlException
	 */
	public function get(array &$data): ?array
	{

	}

	/**
	 * Return page of records collection.
	 * @param array $filters (optional) Associative array. null by default.
	 * @param int $page (optional) Page number, beginning by 1.
	 * @param int $limit (optional) Number of records per page. If 0, then return all rows filtered by $filters. 10 by default.
	 * @param bool $count (optional) If true, return ['results' => <array>, 'count' => <int>], otherwise <array>.
	 * @param string $orderClause (optional) Order clause. null by default.
	 * @return array ['results' => <Page rows>, 'count' => <Total rows>] | <Page rows>
	 */
	public function getPage(array $filters = null, int $page = 1, int $limit = 10, bool $count = true, string $orderClause = null): array
	{

	}
}
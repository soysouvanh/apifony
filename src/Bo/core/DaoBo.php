<?php
namespace App\Bo\core;

use Symfony\Component\HttpFoundation\Response;

/**
 * Dao BO to manage DAO.
 */
class DaoBo extends \App\Bo\AbstractBo {
	/**
	 * Parameter types map between bind type and method parameter type.
	 * @var array 
	 */
	const PARAMETER_TYPES = [
		'i' => 'int',
		'd' => 'float',
		's' => 'string'
	];

	/**
	 * Run ::generateDao business logic.
	 * @return void
	 */
	public function generateCheck(): void
	{
		// Retrieve parameters
		$parameters = &$this->parameters;

		// Check data source
		$ds = &$parameters['dataSourceName'];
		if(!isset(self::$dataSources[$ds])) {
			$this->throwFormDataException('dataSourceName', 'messages', 'DataSourceNameNotFound');
		}

		// Check table name
		if(!isset($parameters['tableName'])) {
			$parameters['tableName'] = null;
		}
	}

	/**
	 * Run main action and return response.
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function generate(): Response
	{
		// Retrieve parameters
		$parameters = &$this->parameters;
		
		// Create data source folder if not exists yet
		$dataSourceName = &$parameters['dataSourceName'];
		$dsFolder = $_ENV['DS_ROOT'] . '/' . $dataSourceName;
		if(!is_dir($dsFolder) && !mkdir($dsFolder)) {
			throw new \App\Exception\FolderException('Create data source folder impossible: ' . $dsFolder);
		}
		
		//Generate DAO
		$methodName = self::$dataSources[$dataSourceName]['type'] . 'GenerateSource';
		$data = $this->$methodName($dataSourceName, $parameters['tableName']);

		// Return view data
		return $this->json($data);
	}

	/**
	 * Generate DAO source(s).
	 * @param string $dataSourceName Data source Name. See "/config/apifony/datasource-<domainName>.php".
	 * @param string $tableName (optional) Table name. If set, generate DAO only for this table, otherwise generate all DAO. 
	 * @return array Generated tables.
	 * @throws \App\Exception\SqlException
	 */
	public function mysqlGenerateSource(string $dataSourceName, string $tableName = null): array
	{
		// Retrieve all tables
		$ds = &$this->$dataSourceName;
		$result = $ds->query('SHOW TABLES');
		if(!$result) {
			$this->throwFormDataException('dataSourceName', 'messages', 'DataSourceEmpty');
		}

		// Return array of arrays: Array ( [0] => Array ( [0] => table1 ) [1] => Array ( [0] => table2 ) [2] => Array ( [0] => table3 ) ) 
		$rows = $result->fetch_all();
		$result->close();
		
		// Retrieve tables
		$tables = [];
		foreach($rows as &$data) {
			$tables[$data[0]] = $data[0];
		}

		// Define DAO to generate
		if($tableName !== null) {
			if(!isset($tables[$tableName])) {
				return [];
			}
			$rows = [$tableName => $tableName];
		}
		else {
			$rows = &$tables;
		}
		
		//Loop on tables
		$daoListGenerated = [];
		foreach($rows as &$tableName) {
			//Retrieve index columns
			$result = $ds->query('SHOW INDEXES FROM ' . $tableName);
			$columns = $result->fetch_all();
			$result->close();
			
			//Build table structure: table should have a least one column
			$keys = [];
			$columnTypes = [];
			$primaryKeys = [];
			$uniqueKeys = [];
			$indexKeys = [];
			$otherKeys = [];
			foreach($columns as &$column) {
				//column = [
				//	0 => Table (ex: customer_form 	)
				//	1 => Non_unique: 0 or 1
				//	2 => Key_name: PRIMARY or column name (ex: customerId)
				//	3 => Seq_in_index: 1, 2, 3, etc. Sequence number for index with multiple column
				//	4 => Column_name (ex: customerId)
				//	5 => Collation (ex: A)
				//	6 => Cardinality (ex: 1)
				//	7 => Sub_part (ex: NULL)
				//	8 => Packed (ex: NULL)
				//	9 => Null
				//	10 => Index_type (ex: BTREE)
				//	11 => Comment
				//	12 => Index_comment
				//	13 => Visible (ex: YES)
				//	14 => Expression (ex: NULL)
				//]
				
				//Case primary key
				if($column[2] === 'PRIMARY') {
					if(!isset($primaryKeys[$column[2]])) {
						$primaryKeys[$column[2]] = [];
					}
					$primaryKeys[$column[2]][$column[4]] = $column[4];
				}
				
				//Case unique
				elseif($column[1] == '0'/* && $column[2] !== 'PRIMARY'*/) {
					if(!isset($uniqueKeys[$column[2]])) {
						$uniqueKeys[$column[2]] = [];
					}
					$uniqueKeys[$column[2]][$column[4]] = $column[4];
				}
				
				//Case index
				elseif($column[1] == '1') {
					if(!isset($indexKeys[$column[2]])) {
						$indexKeys[$column[2]] = [];
					}
					$indexKeys[$column[2]][$column[4]] = $column[4];
				}
				
				//Set keys: primary, unique, index
				$keys[$column[4]] = 1;
			}
			
			//Retrieve all columns
			//DESCRIBE|EXPLAIN <table>
			$result = $ds->query('SHOW COLUMNS FROM ' . $tableName);
			$columns = $result->fetch_all();
			$result->close();
			
			foreach($columns as &$column) {
				//column = [
				//	0 => <Field name>, (ex: customerId)
				//	1 => <Type>, (ex: int(7) unsigned, varchar(64), char(3), datetime)
				//	2 => <Null>, (ex: NO, YES)
				//	3 => <Key>, (ex: PRI (primary key), UNI (unique), MUL (index))
				//	4 => <Default>,
				//	5 => <Extra>
				//]
				
				//Case not a pk, unique and index
				if(!isset($keys[$column[0]])) {
					$otherKeys[$column[0]] = $column[0];
				}
				
				//Define colum type: match on sss(d,d) format
				if(preg_match('/^([^\(]+?)(\((\d+)(,(\d+))?\).*)?$/', explode(' ', $column[1])[0], $matches)) {
					//$matches = Array(
					//	[0] => float(6,2)
					//	[1] => float
					//	[2] => (6,2)
					//	[3] => 6
					//	[4] => ,2
					//	[5] => 2
					//)
					switch($matches[1]) {
						case 'tinyint':
						case 'smallint':
						case 'mediumint':
						case 'int':
							$columnTypes[$column[0]] = [
								'bindType' => 'i',
								'daoType' => 'self::INTEGER_FIELD_TYPE'
							];
							break;
						case 'bigint':
						case 'float':
						case 'double':
						case 'real':
						case 'decimal':
							$columnTypes[$column[0]] = [
								'bindType' => 'd',
								'daoType' => 'self::LONG_FIELD_TYPE'
							];
							break;

						default:
							$columnTypes[$column[0]] = [
								'bindType' => 's',
								'daoType' => 'self::STRING_FIELD_TYPE'
							];
					}
				}

				//Should never occur
				else {
					//String by default
					$columnTypes[$column[0]] = [
						'bindType' => 's',
						'daoType' => 'self::STRING_FIELD_TYPE'
					];
				}
			}
			
			//Generate dao source file
			$daoClass = str_replace(' ', '', ucwords(str_replace('_', ' ', $tableName))) . $_ENV['DAO_CLASS_SUFFIX'];
			ob_start();
			$this->generateTemplate(
				$dataSourceName,
				[
					'dataSourceType' => $this->_dataSourceType,
					'dataSourceName' => $dataSourceName,
					'tableName' => $tableName,
					'daoClass' => $daoClass,
					'primaryKeys' => $primaryKeys,
					'uniqueKeys' => $uniqueKeys,
					'indexKeys' => $indexKeys,
					'otherKeys' => $otherKeys,
					'columnTypes' => $columnTypes
				]
			);
			$source = ob_get_clean();
			$fileName = $_ENV['DS_ROOT'] . '/' . $dataSourceName . '/' . $daoClass . $_ENV['DAO_FILE_EXTENSION'];
			file_put_contents($fileName, $source);
			$daoListGenerated[] = $daoClass;
		}
		
		// Return generated tables list
		return [
			'dataSourceType' => $ds->getConnectionData()['type'],
			'databaseName' => &$dataSourceName,
			'tableList' => array_values($tables),
			'tableTotal' => count($tables),
			'DaoListGenerated' => $daoListGenerated,
			'DaoTotalGenerated' => count($daoListGenerated)
		];
	}

	/**
	 * Generate a part of the source.
	 * @param string $dataSourceName DataSourceName.
	 * @param array $data Input used in org.adventy.model.dao.template.*.
	 * @param string $methodName (optional) Method name: insert, update, delete, get. "" by default to generate all DAO methods.
	 * @return void
	 * @throws \App\Exception\DatabaseConnectionException
	 */
	public function generateTemplate(string $dataSourceName, array $data, string $methodName = ''): void {
		//Generate source
		$fileName = $_ENV['DAO_TEMPLATE_ROOT'] . '/' . self::$dataSources[$dataSourceName]['type'] . '/dao' . ($methodName !== '' ? '-' . $methodName : '') . $_ENV['DAO_TEMPLATE_FILE_EXTENSION'];
		if(is_file($fileName)) {
			require $fileName;
		}
		else {
			echo '/*', $fileName, ' not found!*/';
		}
	}
}
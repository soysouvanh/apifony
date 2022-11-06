<?php 
/**
 * Inputs:
 *	- $data['tableName']
 *	- $data['primaryKeys']
 *	- $data['columnTypes']
 */

// Convert array to string
foreach($data['columnTypes'] as $fieldName => &$typeData) {
	$typeData = '\'' . $fieldName . '\' => ' . $typeData['daoType'];
}
?>
	/**
	 * Constructor.
	 * @param \App\Service\MysqlService $dataSource Data source.
	 * @return void
	 */
	public function __construct(\App\Service\MysqlService &$dataSource)
	{
		$this->dataSource = &$dataSource;
		$this->tableName = '<?= $data['tableName']?>';
		$this->fields = [
			<?= implode(",\n\t\t\t", $data['columnTypes']) . "\n"?>
		];
		$this->primaryKey = <?= isset($data['primaryKeys']['PRIMARY']) ? '[\'' . implode('\', \'', $data['primaryKeys']['PRIMARY']) . '\']' : 'null'?>;
	}
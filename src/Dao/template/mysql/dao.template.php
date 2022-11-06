<?= "<?php \n"?>
namespace App\Dao\ds\<?= $dataSourceName?>;

/**
 * DAO class for "<?= $data['tableName']?>" table.
 * @author Apifony DAO generator by Vincent SOYSOUVANH.
 */
 class <?= $data['daoClass']?> extends \App\Dao\AbstractDao {
<?php 
$this->generateTemplate($dataSourceName, $data, 'constructor');
$this->generateTemplate($dataSourceName, $data, 'insert');
$this->generateTemplate($dataSourceName, $data, 'update');
$this->generateTemplate($dataSourceName, $data, 'updateColumn');
$this->generateTemplate($dataSourceName, $data, 'exists');
$this->generateTemplate($dataSourceName, $data, 'get');
$this->generateTemplate($dataSourceName, $data, 'getByUnique');
$this->generateTemplate($dataSourceName, $data, 'getByIndex');
$this->generateTemplate($dataSourceName, $data, 'getPage');
$this->generateTemplate($dataSourceName, $data, 'delete');
$this->generateTemplate($dataSourceName, $data, 'deleteAll');
$this->generateTemplate($dataSourceName, $data, 'deleteByUnique');
$this->generateTemplate($dataSourceName, $data, 'deleteByIndex');
?>

}
<?= '?>'?>
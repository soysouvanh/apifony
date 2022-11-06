<?php 
namespace App\Trait;

/**
 * Trait used in data source instance (/Service/<Xxx>Service) to access to DAO instance.
 * @require /config/data-sources.<$_SERVER['SERVER_NAME']>.php
 */
trait DsTrait
{
	/**
	 * Connection data:
	 * 	- type: data source type (ex: mysql, postgresql, oracle).
     * 	- driver: database driver if exists (ex: mysql, pgsql, oci), otherwise null.
     * 	- serverName: database host.
     * 	- port: database port number.
     * 	- userName: database user name.
     * 	- password: database password.
     * 	- databaseName: database name.
	 * 	- crypted: true if userName and password are crypted, otherwise false.
	 * @array
	 * @see /config/data-sources.<$_SERVER['SERVER_NAME']>.php
	 */
	protected array $connectionData;

	/**
	 * Return connection data.
	 * @return array
	 */
	public function getConnectionData(): array
	{
		return $this->connectionData;
	}

	/**
	 * Instanciate and return DAO instance if exists.
	 * @param string $instanceName DAO instance name.
	 * @return ?object
	 */
	public function __get(string $instanceName): ?object
	{
		// Case DAO name: xxxDao
		if(isset($instanceName[-3]) && $instanceName[-3] === 'D' && $instanceName[-2] === 'a' && $instanceName[-1] === 'o') {
			// Create DAO instance
			$daoClass = '\\App\\Dao\\ds\\' . $this->connectionData['dataSourceName'] . '\\' . ucfirst($instanceName);
			try {
				$this->$instanceName = new $daoClass($this);
			} catch(\Throwable $e) {
				throw new \App\Exception\DaoException('Instanciate DAO impossible: ' . $daoClass . ' - ' . $e->getMessage());
			}

			// Return DAO instance
			return $this->$instanceName;
		}

		// Case instance name not found
		return null;
	}

	/**
	 * Begin data source transaction.
	 * @param bool $active (optional) If true then auto commit, otherwise false. false by default to disable auto commit.
	 * @return void
	 */
	public function beginTransaction(bool $active = true): void
	{
		// Disable autocommit
		$this->autocommit(!$active);
		
		// Begin transaction
		if($active) {
			$this->begin_transaction();
		}
	}
}
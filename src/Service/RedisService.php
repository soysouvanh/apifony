<?php 
namespace App\Service;

/**
 * Redis manager extending Redis class (phpredis).
 * @require /config/data-sources.<$_SERVER['SERVER_NAME']>.php
 */
class RedisService extends \Redis
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
	 *  - crypted: true if userName and password are crypted, otherwise false.
	 * 	- timeout: (optional) float value in seconds. 0 by default for unlimited.
	 * 	- reserved: (optional) Should be NULL if retry_interval is specified. null by default.
	 * 	- retryInterval: (optional) int value in milliseconds.
	 * 	- readTimeout: (optional) float value in seconds. 0 by default for unlimited.
	 * @array
	 * @see /config/data-sources.<$_SERVER['SERVER_NAME']>.php
	 */
	protected array $connectionData;
	
	/**
	 * Redis constructor.
	 * @param array $connectionData Connection data.
	 * @return void
	 */
	public function __construct(array &$connectionData)
	{
		// Initialize Redis constructor
		parent::__construct();
		
		// Connect to Redis database
		$this->connect(
			$connectionData['serverName'],
			$connectionData['port'],
			$connectionData['timeout'],
			$connectionData['reserved'],
			$connectionData['retryInterval'],
			$connectionData['readTimeout']
		);
		if($connectionData['userName'] !== null || $connectionData['password'] !== null) {
			// Authenticate user
			$this->auth([$connectionData['userName'], $connectionData['password']]);
		}

		// Set attributes
		$this->connectionData = &$connectionData;
	}
	
	/**
	 * Retourne la clé construite à partir de la table et du filtre.
	 * @param string $tableName Nom de table. Exemple: "user".
	 * @param array $filters (optionnel) Filtres ou critères en tableau associatif. Exemple: ['id' => 41, 'city' => 'Vendôme']. null par défaut.
	 * @param string $keyDelimiter (optionnel) Caractère séparateur pour contruire la clé Redis. "|" par défaut.
	 * @return string
	 */
	/*static public function getKey(string $tableName, array $filters = null, string $keyDelimiter = '|'): string
	{
		// Contruit la clé
		$key = $tableName;
		if($filters !== null) {
			$key .= $keyDelimiter . json_encode($filters, JSON_UNESCAPED_SLASHES);
		}
		
		// Retourne la clé
		return $key;
	}*/
	
	/**
	 * Retourne le nombre d'enregistrements total et le nombre de pages total dans un tableau associatif : ['total' => xx, 'totalPage' => xx, 'limit' => xx].
	 * @param string $tableName Nom de table. Exemple: "user".
	 * @param array $filters (optionnel) Filtres ou critères en tableau associatif. Exemple: ['id' => 41, 'city' => 'Vendôme']. null par défaut.*
	 * @param integer $limit (optionnel) Nombre maximum d'enregistrements par page. 100 par défaut.
	 * @return array ['totalRows' => xx, 'totalPage' => xx, 'limit' => xx]
	 */
	/*public function getTotals(string $tableName, array $filters = null, int $limit = 100): array
	{
		return $this->pdo->getTotals($tableName, $filters, $limit);
	}*/
	
	/**
	 * Supprime les données.
	 * @param string $tableName Nom de table. Exemple: "user".
	 * @param array|null $pk (optionnel) Clé primaire ou unique en tableau associatif. Exemple: ['id' => 41, 'city' => 'Vendôme']. null par défaut.
	 * @param bool $onlyCache (optionnel) true pour ne supprimer que le cache, sinon supprime aussi l'enregistrement en base de données. true par défaut.
	 * @param string $keyDelimiter (optionnel) Caractère séparateur pour contruire la clé Redis. "|" par défaut.
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	/*public function remove(string $tableName, array $pk = null, string $keyDelimiter = '|'): void
	{
		// Construit la clé
		$key = $tableName;
		if(!empty($pk)) {
			$key .= $keyDelimiter . json_encode($pk, JSON_UNESCAPED_SLASHES);
		}
		
		// Supprime les données associées à la clé
		$this->del($key);
	}*/
	
	/**
	 * Retourne les données d'un enregistrement par clé primaire.
	 * @param string $tableName Nom de table (ex : "user") ou requête SQL (ex : SELECT * FROM table WHERE id = ?).
	 * @param array $pk Clé primaire ou unique en tableau associatif. Exemple: ['id' => 41, 'city' => 'Vendôme'].
	 * @param int $expiry (optionnel) Temps d'expiration du cache. 0 pour pas de cache par défaut.
	 * @param string $keyDelimiter (optionnel) Caractère séparateur pour contruire la clé Redis. "|" par défaut.
	 * @return array|null
	 * @throws \App\Exception\SqlException
	 */
	/*public function getById(string $tableName, array $pk, int $expiry = 0, string $keyDelimiter = '|'): ?array
	{
		// Contruit la clé
		$key = $tableName . $keyDelimiter . json_encode($pk, JSON_UNESCAPED_SLASHES);
		
		//@todo : à supprimer
		//$this->del($key);
		
		// Récupère les données associées à la clé: données au format JSON
		$data = $this->get($key);
		if($data !== false) {
			return json_decode($data, true);
		}
		
		// Cas : pas de valeur associée à la clé
		// Récupère les données de la base de données
		$sth = $this->pdo->prepare(
			strtoupper(substr(ltrim($tableName), 0, 7)) != 'SELECT '
			? 'SELECT * FROM ' . $tableName . ' WHERE ' . implode(' = ? AND ', array_keys($pk)) . ' = ?'
			: $tableName
		);
		if($sth->execute(array_values($pk)) === false) {
			throw new \App\Exception\SqlException(implode(': ', $sth->errorInfo()));
		}
		$data = $sth->fetch(\PDO::FETCH_ASSOC);
		
		// Cas aucune données
		if($data === false) {
			return null;
		}
		
		// Cas mise en cache
		if($expiry > 0) {
			// Met en cache des données avec une durée d'expiration
			$this->setEx($key, $expiry, json_encode($data, JSON_UNESCAPED_SLASHES));
		}
		
		// Retourne les données
		return $data;
	}*/
	
	/**
	 * Détermine l'existence d'un enregistrement par clé primaire.
	 * @param string $tableName Nom de table. Exemple: "user".
	 * @param array $pk Clé primaire ou unique en tableau associatif. Exemple: ['id' => 41, 'city' => 'Vendôme'].
	 * @param string $keyDelimiter (optionnel) Caractère séparateur pour contruire la clé Redis. "|" par défaut.
	 * @return bool
	 * @throws \App\Exception\SqlException
	 */
	/*public function existsById(string $tableName, array $pk, string $keyDelimiter = '|'): bool
	{
		// Contruit la clé
		$key = $tableName . $keyDelimiter . json_encode($pk, JSON_UNESCAPED_SLASHES);
		
		// Cas clé existe en cache
		if($this->exists($key) > 0) {
			return true;
		}
		
		// Détermine l'existence dans la base de données
		$sth = $this->pdo->prepare('SELECT 1 FROM ' . $tableName . ' WHERE ' . implode(' = ? AND ', array_keys($pk)) . ' = ?');
		if($sth->execute(array_values($pk)) === false) {
			throw new \App\Exception\SqlException(implode(': ', $sth->errorInfo()));
		}
		$data = $sth->fetch(\PDO::FETCH_ASSOC);
		
		// Retourne le résultat
		return !($data === false);
	}*/
	
	/**
	 * Retourne une liste d'enregistrements d'une table.
	 * @param string $tableName Nom de table. Exemple: "user".
	 * @param array $filters (optionnel) Filtres ou critères en tableau associatif. Exemple: ['id' => 41, 'city' => 'Vendôme']. null par défaut.
	 * @param integer $page (optionnel) Numéro de page commençant par 1. 1 par défaut.
	 * @param integer $limit (optionnel) Nombre maximum d'enregistrements par page. 100 par défaut.
	 * @param string $orderClause (optionnel) Clause ORDER BY sans "ORDER BY". Exemple: "country ASC, city DESC". null par défaut.
	 * @param string $keyDelimiter (optionnel) Caractère séparateur pour contruire la clé Redis. "|" par défaut.
	 * @return array
	 * @throws \App\Exception\SqlException
	 */
	/*public function getList(string $tableName, array $filters = null, int $page = 1, int $limit = 100, string $orderClause = null, ?int $offset = null, int $expiry = 0, string $keyDelimiter = '|'): array
	{
		// Construit la clé
		$key = $tableName . $keyDelimiter;
		if($filters !== null) {
			$key .= json_encode($filters, JSON_UNESCAPED_SLASHES) . $keyDelimiter;
		}
		if($orderClause !== null) {
			$key .= $orderClause . $keyDelimiter;
		}
		$key .= $page . $keyDelimiter . $limit;
		
		// Récupère les données associées à la clé
		$data = $this->get($key);
		if($data !== false) {
			return json_decode($data, true);
		}
		
		// Cas : pas de valeur associée à la clé
		$data = $this->pdo->getList($tableName, $filters, $page, $limit, $orderClause, $offset);
		
		// Cas mise en cache
		if($expiry > 0) {
			// Met en cache des données avec une durée d'expiration
			$this->setEx($key, $expiry, json_encode($data, JSON_UNESCAPED_SLASHES));
		}
		
		// Retourne les données
		return $data;
	}*/
	
	/**
	 * Exécute une requête SQL de type SELECT, puis retourne une liste d'enregistrements. Les autres types de requête SQL sont ignorés.
	 * Cette méthode s'utilise de 3 manières différentes :
	 * 1. requête SQL sans paramètre. Par exemple $sql = "SELECT * FROM user WHERE created > '2021-04-01' AND user_id > 3 ORDER BY last_name", et $parameters = null.
	 * 2. requête SQL avec des paramètres nommés. Par exemple $sql = "SELECT * FROM user WHERE created > :created AND user_id > :userId ORDER BY last_name", et $parameters = [':created' => '2021-04-01', ':userId' => 3].
	 * 3. requête SQL avec des marqueurs (ou des indexes commençant par 0):  Par exemple $sql = "SELECT * FROM user WHERE created > ? AND user_id > ? ORDER BY last_name", et $parameters = ['2021-04-01', 3].
	 * @param string $sql Requête SQL. Exemple: "SELECT * FROM user WHERE created > '2021-04-01' AND user_id > 3 ORDER BY last_name" pour une requête SQL sans paramètre, "SELECT * FROM user WHERE created > :created AND user_id > :userId ORDER BY last_name" pour une requête SQL avec des paramètres nommés, "SELECT * FROM user WHERE created > ? AND user_id > ? ORDER BY last_name" pour une requête SQL avec des marqueurs.
	 * @param array $parameters (optionnel) Paramètres en tableau associatif. Exemple: [':created' => '2021-04-01', ':userId' => 3] pour une requête avec des paramétres nommés, ['2021-04-01', 3] pour une requête SQL avec des marqueurs. [] par défaut pour une requête SQL sans paramètre.
	 * @param integer $page (optionnel) Numéro de page commençant par 1. 1 par défaut.
	 * @param integer $limit (optionnel) Nombre maximum d'enregistrements par page. 100 par défaut.
	 * @param string $keyDelimiter (optionnel) Caractère séparateur pour contruire la clé Redis. "|" par défaut.
	 * @return array
	 * @throws \App\Exception\SqlException
	 */
	/*public function getResult(string $sql, array $parameters = [], int $page = 1, int $limit = 100, int $expiry = 0, string $keyDelimiter = '|'): array
	{
		// Construit la clé
		$key = md5($sql) . $keyDelimiter;
		if($parameters !== null) {
			$key .= json_encode($parameters, JSON_UNESCAPED_SLASHES) . $keyDelimiter;
		}
		$key .= $page . $keyDelimiter . $limit;
		
		// Récupère les données associées à la clé
		$data = $this->get($key);
		if($data !== false) {
			return json_decode($data, true);
		}
		
		// Cas pas de valeur associée à la clé
		$data = $this->pdo->getResult($sql, $parameters, $page, $limit);
		
		// Cas mise en cache
		if($expiry > 0) {
			// Met en cache des données avec une durée d'expiration
			$this->setEx($key, $expiry, json_encode($data, JSON_UNESCAPED_SLASHES));
		}
		
		// Retourne les données
		return $data;
	}*/
}
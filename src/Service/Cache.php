<?php 
namespace App\Service;

/**
 * Gestionnaire de cache avec Redis.
 * Cette classe étend la classe Redis (phpredis).
 * @requires /config/<domain>.cache.php
 */
class Cache extends \Redis {
	/**
	 * Informations de connexion à la base de données.
	 * @var array|null
	 */
	static protected ?array $connectionData = null;
	
	/**
	 * Instance de PDO.
	 * @var \App\Service\AbstractPdo
	 */
	protected $pdo = null;
	
	/**
	 * Se connecte à Redis.
	 * @param \App\Service\AbstractPdo $pdo (optionnel) Instance d'un PDO. null par défaut pour n'utiliser que le cache.
	 * @return void
	 */
	public function __construct(\App\Service\AbstractPdo $pdo = null)
	{
		// Initialise Redis en appelant son constructeur
		parent::__construct();
		
		// Cas informations de connexion non encore initialisées
		if(self::$connectionData === null) {
			// Récupère les informations de connexion
			self::$connectionData = \App\Service\AbstractPdo::getConnectionData(
				null,
				null,
				$_SERVER['DOCUMENT_ROOT'] . '/../config/' . $_SERVER['HTTP_HOST'] . '.cache.php'
			);
		}
		
		// Se conecte à Redis
		$this->connect(
			self::$connectionData['serverName'],
			self::$connectionData['port'],
			self::$connectionData['timeout'],
			null,//self::$connectionData['reserved'],
			self::$connectionData['retryInterval'],
			self::$connectionData['readTimeout']
		);
		
		// Cas mot de passe renseigné
		if(self::$connectionData['password'] !== null) {
			// S'authentitfie
			$this->auth([self::$connectionData['userName'], self::$connectionData['password']]);
		}
		
		// Récupère le gestionnaire de la base de données
		$this->pdo = $pdo;
	}
	
	/**
	 * Retourne la clé construite à partir de la table et du filtre.
	 * @param string $tableName Nom de table. Exemple: "user".
	 * @param array $filters (optionnel) Filtres ou critères en tableau associatif. Exemple: ['id' => 41, 'city' => 'Vendôme']. null par défaut.
	 * @param string $keyDelimiter (optionnel) Caractère séparateur pour contruire la clé Redis. "|" par défaut.
	 * @return string
	 */
	static public function getKey(string $tableName, array $filters = null, string $keyDelimiter = '|'): string
	{
		// Contruit la clé
		$key = $tableName;
		if($filters !== null) {
			$key .= $keyDelimiter . json_encode($filters, JSON_UNESCAPED_SLASHES);
		}
		
		// Retourne la clé
		return $key;
	}
	
	/**
	 * Retourne le nombre d'enregistrements total et le nombre de pages total dans un tableau associatif : ['total' => xx, 'totalPage' => xx, 'limit' => xx].
	 * @param string $tableName Nom de table. Exemple: "user".
	 * @param array $filters (optionnel) Filtres ou critères en tableau associatif. Exemple: ['id' => 41, 'city' => 'Vendôme']. null par défaut.*
	 * @param integer $limit (optionnel) Nombre maximum d'enregistrements par page. 100 par défaut.
	 * @return array ['totalRows' => xx, 'totalPage' => xx, 'limit' => xx]
	 */
	public function getTotals(string $tableName, array $filters = null, int $limit = 100): array
	{
		return $this->pdo->getTotals($tableName, $filters, $limit);
	}
	
	/**
	 * Supprime les données.
	 * @param string $tableName Nom de table. Exemple: "user".
	 * @param array|null $pk (optionnel) Clé primaire ou unique en tableau associatif. Exemple: ['id' => 41, 'city' => 'Vendôme']. null par défaut.
	 * @param bool $onlyCache (optionnel) true pour ne supprimer que le cache, sinon supprime aussi l'enregistrement en base de données. true par défaut.
	 * @param string $keyDelimiter (optionnel) Caractère séparateur pour contruire la clé Redis. "|" par défaut.
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	public function remove(string $tableName, array $pk = null, string $keyDelimiter = '|'): void
	{
		// Construit la clé
		$key = $tableName;
		if(!empty($pk)) {
			$key .= $keyDelimiter . json_encode($pk, JSON_UNESCAPED_SLASHES);
		}
		
		// Supprime les données associées à la clé
		$this->del($key);
	}
	
	/**
	 * Retourne les données d'un enregistrement par clé primaire.
	 * @param string $tableName Nom de table (ex : "user") ou requête SQL (ex : SELECT * FROM table WHERE id = ?).
	 * @param array $pk Clé primaire ou unique en tableau associatif. Exemple: ['id' => 41, 'city' => 'Vendôme'].
	 * @param int $expiry (optionnel) Temps d'expiration du cache. 0 pour pas de cache par défaut.
	 * @param string $keyDelimiter (optionnel) Caractère séparateur pour contruire la clé Redis. "|" par défaut.
	 * @return array|null
	 * @throws \App\Exception\SqlException
	 */
	public function getById(string $tableName, array $pk, int $expiry = 0, string $keyDelimiter = '|'): ?array
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
	}
	
	/**
	 * Détermine l'existence d'un enregistrement par clé primaire.
	 * @param string $tableName Nom de table. Exemple: "user".
	 * @param array $pk Clé primaire ou unique en tableau associatif. Exemple: ['id' => 41, 'city' => 'Vendôme'].
	 * @param string $keyDelimiter (optionnel) Caractère séparateur pour contruire la clé Redis. "|" par défaut.
	 * @return bool
	 * @throws \App\Exception\SqlException
	 */
	public function existsById(string $tableName, array $pk, string $keyDelimiter = '|'): bool
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
	}
	
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
	public function getList(string $tableName, array $filters = null, int $page = 1, int $limit = 100, string $orderClause = null, ?int $offset = null, int $expiry = 0, string $keyDelimiter = '|'): array
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
	}
	
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
	public function getResult(string $sql, array $parameters = [], int $page = 1, int $limit = 100, int $expiry = 0, string $keyDelimiter = '|'): array
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
	}
	
	/**
	 * Renseigne les informations de connexion.
	 * @param array $connectionData Nouvelles informations de connexion. 
	 * @return void
	 */
	static public function setConnectionData(array &$connectionData): void
	{
		self::$connectionData = $connectionData;
	}
	
	/**
	 * Initialise les données de formulaire avant l'appel de ::chekParameters.
	 * @param array $formData Données de formulaire à initialiser.
	 * @return void
	 * @see \App\Service\AbstractPdo::initializeSaveConfigurationFormData
	 */
	public function initializeSaveConfigurationFormData(array &$connectionData, array &$formData): void
	{
		// Le nom d'utilisateur n'est pas requis
		$formData['userName']['required']['value'] = false;
		
		// Le mot de passe n'est pas requis
		$formData['password']['required']['value'] = false;
	}
	
	/**
	 * Initialise les paramètres avant l'enregistrement des nouvelles données de configuration.
	 * @param array $parameters Paramètres à initialiser.
	 * @return void
	 * @see \App\Service\AbstractPdo::initializeSaveConfigurationParameters
	 */
	public function initializeSaveConfigurationParameters(array &$parameters): void
	{
		// Initialise le paramètre réservé
		$parameters['reserved'] = null;
		
		// Initialise le nom d'utilisateur
		if($parameters['userName'] == '') {
			$parameters['userName'] = null;
		}
		
		// Initialise le mote de passe
		if($parameters['password'] == '') {
			$parameters['password'] = null;
		}
	}
	
	/**
	 * Met à jour les données d'authentification à la source de données.
	 * @param string $userName Nom d'utilisateur.
	 * @param string $newPassword Nouveau mot de passe.
	 * @param array $formData (optionnel) Form data utilisé en cas d'erreur. null par défaut.
	 * @return void
	 * @see \App\Service\AbstractPdo::updateAuthentication
	 */
	public function updateAuthentication(?string $userName, ?string $newPassword, array &$formData = null): void
	{
		// Récupère la liste des utilisateurs
		// Par sécurité, il ne devrait y en avoir que 2 maximum :
		//	- l'utilisateur par défaut, nommé "default" ;
		//	- un autre utilisateur avec un mot de passe.
		$users = $this->acl('USERS');
		
		// Cas utilisateur non renseigné
		if($userName == '') {
			// Cas mot de passe renseigné
			if(!empty($newPassword)) {
				// Nom d'utilisateur obligatoire
				throw new \App\Exception\FormDataException(json_encode(
					[
						'fieldId' => 'userName',
						'type' => 'required',
						'message' => &$formData['userName']['required']['message'],
						'label' => &$formData['userName']['label']
					],
					\JSON_UNESCAPED_SLASHES
				));
			}
			
			// Supprime tous les utilisateurs, sauf celui par défaut
			foreach($users as &$user) {
				// Cas n'est pas l'utilisateur par défaut
				if($user != 'default') {
					// Supprime l'utilisateur
					$this->acl('DELUSER', $user);
				}
			}
			
			/*
			// Ne fonctionne pas. Il semblerait qu'il faille modifier directement /etc/redis/redis.conf
			// Modifie le mot de passe de l'utilisateur par défaut
			if(!$this->config('SET', 'requirepass', $newPassword !== null ? $newPassword : '')) {
				// Log l'erreur
				\App\Service\LogManager::log($e, $this->pdo);
				
				// Impossible de modifier le mot de passe
				throw new \App\Exception\FormDataException(json_encode(
					[
						'fieldId' => 'password',
						'type' => 'required',
						'message' => &$formData['password']['messages']['SetPasswordImpossible'],
						'label' => &$formData['password']['label']
					],
					\JSON_UNESCAPED_SLASHES
				));
			}*/
		}
		
		// Cas utilisateur renseigné
		else {
			// Cas pas de mot de passe
			if(empty($newPassword)) {
				throw new \App\Exception\FormDataException(json_encode(
					[
						'fieldId' => 'password',
						'type' => 'required',
						'message' => &$formData['password']['required']['message'],
						'label' => &$formData['password']['label']
					],
					\JSON_UNESCAPED_SLASHES
				));
			}
			
			// Cas nouvel utilisateur
			if(!($users[0] == $userName || (isset($users[1]) && $users[1] == $userName))) {
				// Supprime tous les utilisateurs, sauf celui par défaut
				foreach($users as &$user) {
					// Cas n'est pas l'utilisateur par défaut
					if($user != 'default') {
						// Supprime l'utilisateur
						$this->acl('DELUSER', $user);
					}
				}
			}
			
			// Ajoute le nouvel utilisateur (cf. https://redis.io/topics/acl)
			$this->acl('SETUSER', $userName, 'on', '>' . $newPassword, '+@all', '~*');
		}
	}
}
?>
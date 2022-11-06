<?php 
namespace App\Service;

/**
 * Service extendng \PDO class.
 */
class PdoService extends \PDO
{
	/**
	 * Add data source behaviour.
	 */
	use \App\Trait\DsTrait;
	
	/**
	 * Data source constructor.
	 * @param \App\Bo\AbstractBo $bo BO.
	 * @param array $connectionData Connection data.
	 * @return void
	 */
	public function __construct(\App\Bo\AbstractBo &$bo, array &$connectionData)
	{
		// Initialize PDO
		parent::__construct(
			$connectionData['driver'] . ':host=' . $connectionData['serverName'] . ';port=' . $connectionData['port'] . ';dbname=' . $connectionData['databaseName'],
			$connectionData['userName'],
			$connectionData['password'],
			$connectionData['options']
		);
		
		// Set DS trait attributes
		$this->bo = &$bo;
		$this->connectionData = &$connectionData;
	}
	
	/**
	 * Crée et retourne la clause WHERE sans WHERE et les paramètres.
	 * @param array $filters Tableau associatif des filtres (ex : ['email' => ['operator' => 'eq', 'value' => 'test@email.com'], 'ip' => ['operator' => 'eq', 'value' => '127.0.0.1']] ou ['email' => 'test@email.com', 'ip' => '127.0.0.1']). "eq" par défaut si "operator" n'est pas renseigné.
	 * @return array ['whereClause' => ..., 'parameters' => [value1, value2, ...]]
	 */
	/*static public function getWhereClause(array &$filters): array
	{
		// Boucle sur le filtres
		$ands = [];
		$parameters = [];
		foreach($filters as $columnName => &$value) {
			// Cas operator existe
			if(isset($value['operator'])) {
				// Cas opérateur arithmétique
				if(isset(self::$arithmeticOperators[$value['operator']])) {
					$ands[$columnName] = $columnName . ' ' . self::$arithmeticOperators[$value['operator']] . ' ?';
					$parameters[$columnName] = &$value['value'];
				}
				
				// Cas opérateur de chaîne de caractères
				elseif(isset(self::$likeOperators[$value['operator']])) {
					$ands[$columnName] = $columnName . ' ' . self::$likeOperators[$value['operator']] . ' ?';
					if($value['operator'] == 'exists' || $value['operator'] == 'nexists') {
						$parameters[$columnName] = '%' . $value['value'] . '%';
					}
					elseif($value['operator'] == 'starts' || $value['operator'] == 'nstarts') {
						$parameters[$columnName] = $value['value'] . '%';
					}
					elseif($value['operator'] == 'ends' || $value['operator'] == 'nends') {
						$parameters[$columnName] = '%' . $value['value'];
					}
					//elseif($value['operator'] == 'union') {
					//}
				}

				elseif($value['operator'] == 'in') {
					$ands[$columnName] = $columnName . ' IN(?' . str_repeat(',?', count($value['value'])-1) . ')';
					$parameters = array_merge($parameters, $value['value']);
				}
				
				// Cas "eq" ou "=" par défaut
				else {
					$ands[$columnName] = $columnName . ' = ?';
					$parameters[$columnName] = &$value['value'];
				}
			}
			
			// Cas "eq" par défaut
			else {
				$ands[$columnName] = $columnName . ' = ?';
				$parameters[$columnName] = &$value;
			}
		}
		
		// Retourne la clause where
		return [
			'clause' => implode(' AND ', $ands),
			'parameters' => &$parameters
		];
	}*/
	
	/**
	 * Supprime les données.
	 * @param string $tableName Nom de table. Exemple: "user".
	 * @param array $pk Clé primaire ou unique en tableau associatif. Exemple: ['id' => 41, 'city' => 'Vendôme'].
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	/*public function delete(string $tableName, array $pk): void
	{
		// Prépare la requête SQL
		$sth = $this->prepare('DELETE FROM ' . $tableName . ' WHERE ' . implode(' = ? AND ', array_keys($pk)) . ' = ?');
		
		// Exécute la requête SQL
		$this->_execute($sth, $pk, $tableName, 'DELETE');
	}*/

	/**
	 * Supprime les données présentes dans le tableau.
	 * @param string $tableName Nom de table. Exemple: "user".
	 * @param array $in Tableau des clés primaire ou unique en tableau associatif. Exemple: ['id' => [41, 42], 'city' => ['Vendôme','Chambery']].
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	/*public function deleteIn(string $tableName, array $in): void
	{
		// Prépare la requête
		$values = [];
		$sql = 'DELETE FROM ' . $tableName . ' WHERE 1 = 1 ';
		foreach ($in as $column_name => &$column_values) {
			$sql .= 'AND ' . $column_name . ' IN (' . str_repeat('?,', count($column_values) - 1) . '?)';
			$values = array_merge($values, array_values($column_values));
		}

		// Supprime les enregistrements
		$sth = $this->prepare($sql);
		if($sth->execute($values) === false) {
			throw new \App\Exception\SqlException(implode(': ', $sth->errorInfo()));
		}
	}*/
	
	/**
	 * Supprime toutes les données d'une table.
	 * @param string $tableName Nom de table. Exemple: "user".
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	/*public function deleteAll(string $tableName): void
	{
		// Vide la table
		if($this->query('DELETE FROM ' . $tableName) === false) {
			throw new \App\Exception\SqlException('Empty table impossible: ' . $tableName);
		}
	}*/
	
	/**
	 * Spécifique à PostgreSQL. Pour un autre type de base de données, surcharger la méthode.
	 * Retourne une liste d'enregistrements.
	 * @param string $tableName Nom de table. Exemple: "user".
	 * @param array $filters (optionnel) Filtres ou critères en tableau associatif. Exemple: ['id' => 41, 'city' => 'Vendôme']. null par défaut.
	 * @param integer $page (optionnel) Numéro de page commençant par 1. 1 par défaut.
	 * @param integer $limit (optionnel) Nombre maximum d'enregistrements par page. 10 par défaut et limité à 1000.
	 * @param string $orderBy (optionnel) Clause ORDER BY sans "ORDER BY". Exemple: "country ASC, city DESC". null par défaut.
	 * @return array
	 * @throws \App\Exception\SqlException
	 */
	/*public function getList(string $tableName, array $filters = null, int $page = 1, int $limit = 10, ?string $orderBy = null, ?int $offset = null): array
	{
		// Affecte les valeurs par défaut : $page = 1, $limit = 10 (1000 max)
		if($limit <= 0 || $limit > 1000) {
			$limit = 10;
		}
		if($page <= 0) {
			$page = 1;
		}
		if($offset !== null && $offset < 0) {
			$offset = 0;
		}
		
		// Construit la requête SQL
		$sql = 'SELECT * FROM ' . $tableName;
		if($filters !== null) {
			$whereClause = self::getWhereClause($filters);
			$sql .= ' WHERE ' . $whereClause['clause'];
			$filters = &$whereClause['parameters'];
		}
		else {
			$filters = [];
		}
		if($orderBy !== null) {
			$sql .= ' ORDER BY ' . $orderBy;
		}
		$sql .= ' OFFSET ? LIMIT ?';

		// Ajoute les paramètres de pagination
		$filters['offset'] = $offset !== null ? $offset : ($page -1) * $limit;
		$filters['limit'] = &$limit;

		// Excécute la requête préparée
		$sth = $this->prepare($sql);
		if($sth->execute(array_values($filters)) === false) {
			throw new \App\Exception\SqlException(implode(': ', $sth->errorInfo()));
		}
		
		// Retourne le résultat
		$rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
		if($rows === false) {
			throw new \App\Exception\SqlException(implode(': ', $sth->errorInfo()));
		}
		return $rows;
	}*/
	
	/**
	 * Retourne le nombre d'enregistrements.
	 * @param string $tableName Nom de table OU sql. Exemple: "user" ou "SELECT nom FROM user".
	 * @param array $filters (optionnel) Filtres ou critères en tableau associatif. Exemple: ['id' => 41, 'city' => 'Vendôme']. null par défaut.
	 * @return int
	 * @throws \App\Exception\SqlException
	 */
	/*public function count(string $tableName, array $filters = null): int
	{
		// Construit la requête SQL
		// COUNT(*) : KO pour MSSQL (Belair)
		// COUNT(*) AS n : OK
		// FROM (table) : KO
		// FROM (SELECT * FROM table) : KO
		// FROM (SELECT * FROM table) AS _table : OK
		$isSelect = strtoupper(substr(trim($tableName), 0, 6)) == 'SELECT';



		// // Supprime la clause order by si elle est présente
		// Supprime la clause order by si elle est présente
		$orderByPos = stripos(strrchr($tableName, 'WHERE'), 'ORDER BY');
		if($isSelect && $orderByPos) {
			$sqlSecondPart = substr($tableName, $orderByPos, strlen($tableName) - $orderByPos);
			// Verifie si le order by est un select d'un select
			if(substr_count($sqlSecondPart, ')') >= substr_count($sqlSecondPart, '(')) {
				$tableName = preg_replace('/ORDER BY [a-zA-Z\d\._]+(\s+(ASC|DESC)?)?(\s*,\s*[a-zA-Z\d\._]+(\s+(ASC|DESC)?)*)/', '', $tableName);
			}
		} 
		// if($isSelect && stripos(strrchr($tableName, 'WHERE'), 'ORDER BY')) {
		// 	$tableName = preg_replace('/ORDER BY [a-zA-Z\d\._]+(\s+(ASC|DESC)?)?(\s*,\s*[a-zA-Z\d\._]+(\s+(ASC|DESC)?)*)/', '', $tableName);
		// }

		// Construit la requête selon isSelect
		$sql = !$isSelect
			? 'SELECT COUNT(*) AS n FROM ' . $tableName
			: 'SELECT COUNT(*) AS n FROM (' . $tableName . ') AS _table';

		// Cas existe un moins un filtre
		if($filters !== null && $filters !== []) {
			// Cas nom de table passé en paramètre
			if(!$isSelect) {
				// Complète la requête SQL avec les filtres
				$sql .= ' WHERE ' . implode(' = ? AND ', array_keys($filters)) . ' = ?';
			}

			// Excécute la requête préparée
			$sth = $this->prepare($sql);
			if($sth->execute(array_values($filters)) === false) {
				throw new \App\Exception\SqlException(implode(': ', $sth->errorInfo()));
			}
			
			// Récupère le résultat de la requête SQL
			$data = $sth->fetch(\PDO::FETCH_NUM);
		}
		
		// Cas aucun filtre
		else {
			// Exécute la requête SQL et récupère le résultat
			$data = $this->query($sql)->fetch(\PDO::FETCH_NUM);
		}
		
		// Cas erreur
		if($data === false) {
			throw new \App\Exception\SqlException(implode(': ', $sth->errorInfo()));
		}
		
		// Retourne le nombre d'enregistrement
		return  $data[0];
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
		// Recupère le nombre d'enregistrement
		$total = $this->count($tableName, $filters);
		
		// Retourne les totaux
		return ['totalRows' => &$total, 'totalPage' => ceil($total / $limit), 'limit' => $limit];
	}*/
	
	/**
	 * Spécifique à PostgreSQL. Pour un autre type de base de données, il faudra surcharger la méthode.
	 * Exécute une requête SQL de type SELECT, puis retourne une liste d'enregistrements. Les autres types de requête SQL sont ignorés.
	 * Cette méthode s'utilise de 3 manières différentes :
	 * 1. requête SQL sans paramètre. Par exemple $sql = "SELECT * FROM user WHERE created > '2021-04-01' AND user_id > 3 ORDER BY last_name", et $parameters = null.
	 * 2. requête SQL avec des paramètres nommés. Par exemple $sql = "SELECT * FROM user WHERE created > :created AND user_id > :userId ORDER BY last_name", et $parameters = [':created' => '2021-04-01', ':userId' => 3].
	 * 3. requête SQL avec des marqueurs (ou des indexes commençant par 0):  Par exemple $sql = "SELECT * FROM user WHERE created > ? AND user_id > ? ORDER BY last_name", et $parameters = ['2021-04-01', 3].
	 * @param string $sql Requête SQL. Exemple: "SELECT * FROM user WHERE created > '2021-04-01' AND user_id > 3 ORDER BY last_name" pour une requête SQL sans paramètre, "SELECT * FROM user WHERE created > :created AND user_id > :userId ORDER BY last_name" pour une requête SQL avec des paramètres nommés, "SELECT * FROM user WHERE created > ? AND user_id > ? ORDER BY last_name" pour une requête SQL avec des marqueurs.
	 * @param array $parameters (optionnel) Paramètres en tableau associatif. Exemple: [':created' => '2021-04-01', ':userId' => 3] pour une requête avec des paramétres nommés, ['2021-04-01', 3] pour une requête SQL avec des marqueurs. [] par défaut pour une requête SQL sans paramètre.
	 * @param integer $page (optionnel) Numéro de page commençant par 1. 1 par défaut.
	 * @param integer $limit (optionnel) Nombre maximum d'enregistrements par page. 10 par défaut et limité à 1000.
	 * @param string $orderBy (optionnel) Clause ORDER BY sans "ORDER BY". Exemple: "country ASC, city DESC". null par défaut.
	 * @param integer $offset (optionnel) Offset commençant par 0. null par défaut.
	 * @param integer $totalRows (optionnel) Valeur de sortie du nombre total d'enregistrements. null par défaut.
	 * @param integer $totalPage (optionnel) Valeur de sortie du nombre total de pages. null par défaut.
	 * @return array
	 * @throws \App\Exception\SqlException
	 */
	/*public function getResult(string $sql, array $parameters = [], int $page = 1, int $limit = 10, ?string $orderBy = null, ?int $offset = null, &$totals = null): array
	{
		// Affecte les valeurs par défaut : $page = 1, $limit = 10 (1000 max)
		if($limit <= 0 || $limit > 1000) {
			$limit = 10;
		}
		elseif($limit > 1000) {
			$limit = 1000;
		}
		if($page <= 0) {
			$page = 1;
		}
		if($offset !== null && $offset < 0) {
			$offset = 0;
		}
		
		// Récupère le nombre d'enregistrements et le nombre de pages
		// Bug PHP ? __call + paramètre passé par référence : ne fonctionne que si le paramètre est un objet
		//$total = $this->count($sql, $parameters);
		//$totals = ['totalRows' => &$total, 'totalPage' => ceil($total / $limit)];
		$totals = $totals ?: (object)[];
		$totals->totalRows = $this->count($sql, $parameters);
		$totals->totalPage = ceil($totals->totalRows / $limit);
		
		// Cas page ou offset en dehors de la limite de recherche
		//if($page > $totals['totalPage'] || $offset > $totals['totalRows'] - 1) {
		if($page > $totals->totalPage || $offset > $totals->totalRows - 1) {
			// Retourne un résultat vide
			return [];
		}
		
		// Rajoute la clause ORDER BY si elle existe
		if($orderBy !== null && $orderBy !== '') {
			$sql .= ' ORDER BY ' . $orderBy;
		}
		
		// Rajoute la pagination
		$sql .= ' OFFSET ? LIMIT ?';
		$parameters['offset'] = $offset !== null ? $offset : ($page -1) * $limit;
		$parameters['limit'] = &$limit;

		// Exécute la requête préparée
		$sth = $this->prepare($sql);
		if($sth->execute(array_values($parameters)) === false) {
			throw new \App\Exception\SqlException(implode(': ', $sth->errorInfo()));
		}

		// Retourne le résultat
		$rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
		if($rows === false) {
			throw new \App\Exception\SqlException(implode(': ', $sth->errorInfo()));
		}
		return $rows;
	}*/
	
	/**
	 * Version qui mesure le temps de réponse + tracking dans adm_tracking_belair. A utiliser dans les API.
	 * Exécute une requête SQL de type SELECT, puis retourne une liste d'enregistrements. Les autres types de requête SQL sont ignorés.
	 * Cette méthode s'utilise de 3 manières différentes :
	 * 1. requête SQL sans paramètre. Par exemple $sql = "SELECT * FROM user WHERE created > '2021-04-01' AND user_id > 3 ORDER BY last_name", et $parameters = null.
	 * 2. requête SQL avec des paramètres nommés. Par exemple $sql = "SELECT * FROM user WHERE created > :created AND user_id > :userId ORDER BY last_name", et $parameters = [':created' => '2021-04-01', ':userId' => 3].
	 * 3. requête SQL avec des marqueurs (ou des indexes commençant par 0):  Par exemple $sql = "SELECT * FROM user WHERE created > ? AND user_id > ? ORDER BY last_name", et $parameters = ['2021-04-01', 3].
	 * @param string $sql Requête SQL. Exemple: "SELECT * FROM user WHERE created > '2021-04-01' AND user_id > 3 ORDER BY last_name" pour une requête SQL sans paramètre, "SELECT * FROM user WHERE created > :created AND user_id > :userId ORDER BY last_name" pour une requête SQL avec des paramètres nommés, "SELECT * FROM user WHERE created > ? AND user_id > ? ORDER BY last_name" pour une requête SQL avec des marqueurs.
	 * @param array $parameters (optionnel) Paramètres en tableau associatif. Exemple: [':created' => '2021-04-01', ':userId' => 3] pour une requête avec des paramétres nommés, ['2021-04-01', 3] pour une requête SQL avec des marqueurs. null par défaut pour une requête SQL sans paramètre.
	 * @param integer $page (optionnel) Numéro de page commençant par 1. Si $offset est non null, alors $page est ignoré. 1 par défaut.
	 * @param integer $limit (optionnel) Nombre maximum d'enregistrements par page. 10 par défaut et limité à 1000.
	 * @param string $orderBy (optionnel) Clause ORDER BY sans "ORDER BY". Exemple: "country ASC, city DESC". null par défaut.
	 * @param integer $offset (optionnel) Offset commençant par 0. null par défaut.
	 * @param integer $totalRows (optionnel) Valeur de sortie du nombre total d'enregistrements. null par défaut.
	 * @param integer $totalPage (optionnel) Valeur de sortie du nombre total de pages. null par défaut.
	 * @return array
	 * @throws \App\Exception\SqlException
	 */
	/*protected function getMeasuredResult(string $sql, array $parameters = [], int $page = 1, int $limit = 10, ?string $orderBy = null, ?int $offset = null, &$totals = null): array
	{
		return $this->getResult($sql, $parameters, $page, $limit, $orderBy, $offset, $totals);
	}*/
	
	/**
	 * Insère un enregistrement dans une table.
	 * @param string $tableName Nom de la table.
	 * @param array $data Tableau associatif des données à insérer.
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	/*public function insert(string $tableName, array $data): void
	{
		// Prépare la requête SQL
		$sth = $this->prepare('INSERT INTO ' . $tableName . '(' . implode(',', array_keys($data)) . ') VALUES (' . implode(', ', array_fill(0, count($data) , '?')) . ')');

		// Exécute la requête SQL
		$this->_execute($sth, $data, $tableName, 'INSERT');
	}*/
	
	/**
	 * Modifie un enregistrement dans une table.
	 * @param string $tableName Nom de la table.
	 * @param array $data Tableau associatif des données à modifier (ex : ['user_name' => '', 'password' => 'xxx']).
	 * @param array $pk Clé primaire en tableau associatif (ex : ['id_client' => 42]).
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	/*public function update(string $tableName, array $data, array $pk): void
	{
		// Prépare la requête SQL
		$where = ' WHERE ' . implode(' = ? AND ', array_keys($pk)) . ' = ?';
		$sth = $this->prepare('UPDATE ' . $tableName . ' SET ' . implode(' = ?, ', array_keys($data)) . ' = ?' . $where);
		
		// Ajoute la clé primaire aux données préparées
		foreach($pk as $key => &$value) {
			$data['pk_' . $key] = &$value;
		}
		
		// Execute la requête
		$this->_execute($sth, $data, $tableName, 'UPDATE');
	}*/
	
	/**
	 * Détermine l'existence d'au moins un enregistrement en base de données.
	 * @param string $tableName Nom de la table.
	 * @param array $data Tableau associatif des données à filtrer.
	 * @return bool
	 * @throws \App\Exception\SqlException
	 */
	/*public function existsBy(string $tableName, array $data): bool
	{
		// Prépare la requête SQL
		$sth = $this->prepare('SELECT 1 FROM ' . $tableName . ' WHERE ' . implode(' = ? AND ', array_keys($data)) . ' = ?');
		
		// Exécute la requête SQL
		if($sth->execute(array_values($data)) === false) {
			throw new \App\Exception\SqlException(implode(': ', $sth->errorInfo()));
		}
		
		// Récupère le résultat de la requête SQL
		return $sth->fetch(\PDO::FETCH_NUM) !== false;
		
		// La documentation PHP dit que false est en cas d'erreur, mais ici false indique aucun résultat
		//$rows = $sth->fetch(\PDO::FETCH_NUM);
		//if($rows === false) {
		//	throw new \App\Exception\SqlException(implode(': ', $sth->errorInfo()));
		//}
		//return !empty($rows);
	}*/
	
	/**
	 * Retourne un identifiant de table pour une insertion.
	 * @param string $tableName Nom de la table.
	 * @param string $columnName Nom de la colonne utilisé comme identifiant.
	 * @return int
	 */
	/*public function getNewId(string $tableName, string $columnName): int
	{
		// Recherche un identifiant libre
		$data = $this->query('SELECT MIN(' . $columnName . '+1) AS ' . $columnName . ' FROM ' . $tableName . ' WHERE ' . $columnName . '+1 NOT IN (SELECT ' . $columnName . ' FROM ' . $tableName . ')')->fetch(\PDO::FETCH_ASSOC);
		
		// Retourne l'identifiant libre trouvé
		return isset($data[$columnName]) ? $data[$columnName] : 1;
	}*/
	
	/**
	 * Retourne la contrainte d'une table.
	 * @param string $tableName Nom de la table.
	 * @param string $constraintType (optionnel) Type de contrainte de la table (ex : "PRIMARY KEY", UNIQUE, null). "PRIMARY KEY" par défaut.
	 * @param string $schema (optionnel) Schéma de la table. "public" par défaut.
	 * @return array [[column_name, data_type, data_type_name], ...] (ex : [[column_name = 'client_id', data_type => 'smallInt', data_type_name => 'int2']]).
	 * @throws \App\Exception\SqlException
	 */
	/*public function getConstraintNames(string $tableName, ?string $constraintType = 'PRIMARY KEY', $schema = 'public'): array
	{
		// Excécute la requête préparée
		$sth = $this->prepare(
			'SELECT
				c.column_name,
				tc.constraint_type,
				tc.constraint_name,
				c.data_type,
				c.udt_name AS data_type_name
			FROM
				information_schema.table_constraints tc,
				information_schema.constraint_column_usage ccu,
				information_schema.columns c
			WHERE
				tc.table_name = ?
				AND tc.constraint_schema = ?
			'
			. ($constraintType !== null ? '	AND tc.constraint_type = ?' : '')
			. '
				AND tc.constraint_name = ccu.constraint_name
				AND ccu.table_catalog = tc.table_catalog
				AND ccu.table_schema = tc.table_schema
				AND ccu.table_name = tc.table_name
				AND c.table_catalog = tc.table_catalog
				AND c.table_schema = tc.table_schema
				AND c.table_name = tc.table_name
				AND c.column_name = ccu.column_name'
		);
		if($sth->execute($constraintType !== null ? [$tableName, $schema, $constraintType] : [$tableName, $schema]) === false) {
			throw new \App\Exception\SqlException(implode(': ', $sth->errorInfo()));
		}

		// Retourne le résultat
		$rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
		if($rows === false) {
			throw new \App\Exception\SqlException(implode(': ', $sth->errorInfo()));
		}
		return $rows;
	}*/
	
	/**
	 * Retourne la liste des fonctions.
	 * @param string $returnType (optionnel) Type de retour de la fonction. "trigger" par défaut.
	 * @param string $schema (optionnel) Schéma. "public" par défaut.
	 * @return array (ex : ['trg_create_cache', 'trg_delete_cache', 'trg_update_cache'])
	 * @throws \App\Exception\SqlException
	 */
	/*public function getFunctions(string $returnType = 'trigger', string $schema = 'public'): array
	{
		// Excécute la requête préparée
		$sth = $this->prepare(
			'SELECT
				p.proname AS name
				--,n.nspname AS function_schema
				--,l.lanname AS function_language
				--,case when l.lanname = \'internal\' then p.prosrc
				--     else pg_get_functiondef(p.oid)
				--     end AS definition,
				--,pg_get_function_arguments(p.oid) AS function_arguments
				--,t.typname AS return_type
			FROM
				pg_proc p
				LEFT JOIN pg_namespace n ON p.pronamespace = n.oid
				LEFT JOIN pg_language l ON p.prolang = l.oid
				LEFT JOIN pg_type t ON t.oid = p.prorettype 
			WHERE
				n.nspname = ?
				AND t.typname = ?
				AND n.nspname NOT IN (\'pg_catalog\', \'information_schema\')
			ORDER BY
				--function_schema,
				name'
		);
		if($sth->execute([$schema, $returnType]) === false) {
			throw new \App\Exception\SqlException(implode(': ', $sth->errorInfo()));
		}

		// Retourne le résultat
		$rows = $sth->fetchAll(\PDO::FETCH_COLUMN, 0);
		if($rows === false) {
			throw new \App\Exception\SqlException(implode(': ', $sth->errorInfo()));
		}
		return $rows;
	}*/
	
	/**
	 * Ajoute un trigger à une table.
	 * @param string $triggerName Nom du trigger (ex : trg_create_cache).
	 * @param string $tableName Nom de la table (ex : adm_authentication).
	 * @param string $action Nom de l'action SQL (ex : INSERT, UPDATE, DELETE).
	 * @param string $event (optionnel) Nom de la table (ex : BEFORE, AFTER). "AFTER par défaut".
	 * @param string $schema (optionnel) Schéma. "public" par défaut.
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	/*public function addTrigger(string $triggerName, string $tableName, string $action, string $event = 'AFTER', string $schema = 'public'): void
	{
		// Il ne semble pas possible de créer un trigger en paramétré de cette façon
		// Prépare les paramètres
		//$trigger = strtolower('trigger_' . $tableName . '_' . $event . '_' . $action);
		//$table = $schema . '.' . $tableName;
		//$function = $schema . '.' . $triggerName . '()';
		
		// Excécute la requête préparée
		//$sth = $this->prepare('CREATE TRIGGER ? ' . $event . ' ' . $action . ' ON ? FOR EACH ROW EXECUTE FUNCTION ' . $function);
		//if($sth->execute([$trigger, $table, $function]) === false) {
		//	throw new \App\Exception\SqlException(implode(': ', $sth->errorInfo()));
		//}
		
		// Construit la requête SQL
		$sql = 'CREATE TRIGGER ' . strtolower('trigger_' . $tableName . '_' . $event . '_' . $action) . ' ' . $event . ' ' . $action . ' ON ' . $schema . '.' . $tableName . ' FOR EACH ROW EXECUTE FUNCTION ' . $schema . '.' . $triggerName . '(\'' . $_SERVER['REQUEST_SCHEME'] . '\',\'' . $_SERVER['HTTP_HOST'] . '\')';
		
		// Excécute la requête SQL
		if($this->query($sql) === false) {
			throw new \App\Exception\SqlException('Create trigger impossible: ' . $sql);
		}
	}*/
	
	/**
	 * Supprime un trigger d'une table.
	 * @param string $tableName Nom de la table (ex : adm_authentication).
	 * @param string $action Action SQL : insert, update, delete.
	 * @param string $event (optionnel) Evénement : BEBORE, AFTER. AFTER par défaut.
	 * @return void
	 * @throws \App\Exception\SqlException
	 */
	/*public function removeTrigger(string $tableName, string $action, string $event = 'AFTER'): void
	{
		// Il ne semble pas possible de créer un trigger en paramétré de cette façon
		// Prépare les paramètres
		//$triggerName = strtolower('trigger_' . $tableName . '_' . $event . '_' . $action);
		
		// Excécute la requête préparée
		//$sth = $this->prepare('DROP TRIGGER IF EXISTS ? ON ?');
		//if($sth->execute([$triggerName, $tableName]) === false) {
		//	throw new \App\Exception\SqlException(implode(': ', $sth->errorInfo()));
		//}
		
		// Construit la requête SQL
		$sql = 'DROP TRIGGER IF EXISTS ' . strtolower('trigger_' . $tableName . '_' . $event . '_' . $action) . ' ON ' . $tableName;
		
		// Excécute la requête SQL
		if($this->query($sql) === false) {
			throw new \App\Exception\SqlException('Remove trigger impossible: ' . $sql);
		}
	}*/
	
	/**
	 * Retourne la liste des colonnes d'une table.
	 * @param string $tableName Nom de table.
	 * @return array
	 */
	/*public function getColumnNames(string $tableName): array
	{
		// Excécute la requête préparée
		$sth = $this->prepare('SELECT column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ?');
		$sth->execute([$tableName]);
		
		// Retourne le résultat
		return $sth->fetchAll();
	}*/

	/**
	 * Retourne l'enregistrement par sa clé primaire si trouvé, sinon null.
	 * @param string $tableName Nom de table (Ex : adm_administrator).
	 * @param array $pk Clé primaire ou indexe unique en tableau associatif (Ex : ['administrator_id' => 1]).
	 * @return array|null
	 * @throws \App\Exception\SqlException
	 */
	/*public function get(string $tableName, array &$pk): ?array
	{
		// Récupère les données de la base de données
		$sth = $this->prepare('SELECT * FROM ' . $tableName . ' WHERE ' . implode(' = ? AND ', array_keys($pk)) . ' = ?');

		if($sth->execute(array_values($pk)) === false) {
			throw new \App\Exception\SqlException(implode(': ', $sth->errorInfo()));
		}
		$data = $sth->fetch(\PDO::FETCH_ASSOC);
		
		// Cas aucune donnée
		if($data === false) {
			return null;
		}

		// Retourne les données
		return $data;
	}*/
	
	/**
	 * Exécute la requête SQL paramétrée.
	 * @param \PDOStatement $sth Instruction PDO.
	 * @param array $data Données paramétrées.
	 * @param string $tableName Nom de table.
	 * @param string $action Action de la requête : INSERT, UPDATE, DELETE.
	 * @return void
	 * @throws \Exception
	 * @throws \App\Exception\SqlException
	 */
	/*protected function _execute(\PDOStatement $sth, array &$data, string $tableName, string $action): void
	{
		try{
			// Exécute la requête SQL
			if($sth->execute(array_values($data)) === false) {
				throw new \App\Exception\SqlException(implode(': ', $sth->errorInfo()));
			}
		} catch(\Exception $e) {
			// Cas d'erreur SQL
			if($e instanceof \App\Exception\SqlException) {
				// Relance l'erreur SQL
				throw $e;
			} 
			
			// Cas d'erreurs non trigger
			// Classes d'erreurs trigger :
			//   - 09xxx: Triggered Action Exception
			//   - 27xxx: Triggered Data Change Violation
			//   - 38xxx: External Routine Exception
			//   - 39xxx: External Routine Invocation Exception
			$errorClass = substr($e->getCode(), 0, 2);
			if(!($errorClass == '09' || $errorClass == '27' || $errorClass == '38' || $errorClass == '39')) {
				// Relance l'erreur SQL
				throw $e;
			}
			
			// A partir d'ici, ne traite que le trigger
			// Trace l'erreur
			\App\Service\LogManager::log($e,  $this instanceof \App\Service\Middleware ? $this : new \App\Service\Middleware($this->baseController));
			
			// Supprime temporairement le trigger de la table.
			$action = strtolower($action);
			$this->removeTrigger($tableName, $action);
			
			// Trace la suppression du trigger
			//$exception = get_class($e);
			//\App\Service\LogManager::log(new $exception('Trigger trg_' . strtolower($action === 'INSERT' ? 'CREATE' : $action) . '_cache removed from ' . $tableName),  $this instanceof \App\Service\Middleware ? $this : new \App\Service\Middleware($this->baseController));
			
			// Supprime le cache lié à l'enregistrement
			(new \App\Service\Cache($this))->remove(\App\Service\Cache::getKey($tableName, $data));

			try {
				// Relance la requête SQL
				if($sth->execute(array_values($data)) === false) {
					throw new \App\Exception\SqlException(implode(': ', $sth->errorInfo()));
				}
			} finally {
				// Rattache le trigger à la table
				$this->addTrigger('trg_' . ($action === 'insert' ? 'create' : $action) . '_cache', $tableName, $action);
			}
		}
	}*/
	
	/**
	 * Retourne la liste de toutes les tables de la base de données.
	 * @param array $filters Filtre (ex : ['TABLE_CATALOG' => 'DEVAPI01', TABLE_SCHEMA' => 'dbo', 'TABLE_NAME' => 'ZX056API', 'TABLE_TYPE' => 'BASE TABLE']). ['TABLE_TYPE' => 'BASE TABLE'] par défaut.
	 * @return array
	 */
	/*public function getTables(array $filters = ['TABLE_TYPE' => 'BASE TABLE']): array
	{
		// Construit la requête SQL
		$sql = 'SELECT * FROM INFORMATION_SCHEMA.TABLES';
		if(!empty($filters)) {
			$clauses = [];
			foreach($filters as $name => &$value) {
				$clauses[] = $name . ' = ?';
			}
			$sql .= ' WHERE ' . implode(' AND ', $clauses);
		}

		// Lance la requête SQL
		return $this->getResult($sql, $filters, 1, 1000, 'TABLE_SCHEMA, TABLE_NAME');
	}*/
	
	/**
	 * Construit l'exception au format de réponse Middleware.
	 * @param \Throwable $e Exception.
	 * @return \Throwable
	 */
	/*public function toException(\Throwable $e): \Throwable
	{
		// Cas d'erreur connue : colonne inconnue
		// 42703 pour PostgreSQL
		// 42S22 pour MSSQL
		$code = $e->getCode();
		if($code == '42703' || $code == '42S22') {		
			if(!isset($_SERVER['HTTP_USER_AGENT']) || (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] !== $this->baseController::INTEGRATION_TEST_CLIENT)) {
				// Logue l'erreur technique en base de données ou dans le dossier /log/
				\App\Service\LogManager::log($e, $this instanceof \App\Service\Middleware ? $this : new \App\Service\Middleware($this->baseController));
			}

			// Crée l'exception
			$re = [
				'42703' => '/(\"|«\s)([^\s\']+)(\"|\s»)/',
				'42S22' => '/\'([^\s\']+)\'\.$/'
			];
			$matches = null;
			preg_match($re[$code], $e->getMessage(), $matches);
			$e = new \App\Exception\BadRequestException('Undefined column: ' . $matches[0]);

		}
	
		// Cas hors indexe/numéro de colonne : SQLSTATE[42000]: Invalid column reference: 7 ERROR: ORDER BY position 123 is not in select list LINE 1: SELECT * FROM type_courtier ORDER BY 123 OFFSET $1 LIMIT $2
		// 42000 pour MSSQLServer
		elseif($code == '42000') {
			// Récupère le champ invalide
			$matches = null;
			preg_match('/ position ([^\s,]+)/', $e->getMessage(), $matches);
			
			// Charge la description du paramètre sortBy
			$_formData = [];
			require  $_SERVER['DOCUMENT_ROOT'] . '/../src/field/sortBy.field.php';
			
			// Crée l'exception
			$e = new \App\Exception\FormDataException(json_encode(['fieldId' => 'sortBy', 'type' => 'format', 'message' => &$_formData['sortBy']['format']['message'], 'label' => &$_formData['sortBy']['label']], \JSON_UNESCAPED_SLASHES));
		}

		// Cas erreur technique
		else {
			$code = (int)$code;
			if($code < 100 || $code >= 500) {
				// Cas code d'erreur incorrect, car il doit être composé de 3 chiffres
				if($code < 100 || $code > 527) {
					$e = new $e($code . ' Internal Server Error : ' . $e->getMessage(), 500);
				}
			}
		}

		// Retourne la nouvelle exception
		return $e;
	}*/

	/**
	 * Retourne la liste des utilisateurs de la source de données (ici PostgreSQL) ainsi que leurs attributs.
	 * @param string $role (optionnel) Rôle à retourner. nul par défaut pour retournes tous les rôles.
	 * @return array
	 */
	/*public function getRoles(string $role = null): array
	{
		// Lance la requête SQL
		return $this->getResult(
			'SELECT
				usename AS role_name,
				CASE 
				   WHEN usesuper AND usecreatedb THEN 
					 CAST(\'superuser, create database\' AS pg_catalog.text)
				   WHEN usesuper THEN 
					  CAST(\'superuser\' AS pg_catalog.text)
				   WHEN usecreatedb THEN 
					  CAST(\'create database\' AS pg_catalog.text)
				   ELSE 
					  CAST(\'\' AS pg_catalog.text)
				END role_attributes
			FROM pg_catalog.pg_user'
			. ($role !== null ? ' WHERE usename = ?' : ''),
			$role !== null ? ['usename' => &$role] : [],
			1,
			1000,
			'role_name'
		);
	}*/
	
	/**
	 * Version qui mesure le temps de réponse + tracking dans adm_tracking_<belair>. A utiliser dans les API.
	 * Retourne les données d'un enregistrement par sa clé primaire ou une colonne de contrainte unique.
	 * @param string $sql Requête SQL paramétré. Ex : SELECT * FROM TIERSAPI WHERE B_NUMTIERS = ?
	 * @param array $parameters Liste des paramètres, en tableau associatif ou indexé. Ex : ['id_courtier' => 10355] ou [10355].
	 * @return array|null
	 */
	/*protected function getById(string $sql, array $parameters): ?array
	{
		// Exécute la requête préparée
		$stmt = $this->prepare($sql);
		$stmt->execute(array_values($parameters));
		
		// Récupère le résultat
		$data = $stmt->fetch();
		
		// Retourne le résultat
		return $data !== false ? $data : null;
	}*/
}
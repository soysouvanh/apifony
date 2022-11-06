<?php 
namespace App\Trait;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;

trait AspectTrait
{
	/**
	 * Request instance.
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	public readonly \Symfony\Component\HttpFoundation\Request $request;

	/**
	 * Bo instance from "/src/Bo/" folder.
	 * @var \App\Bo\AbstractBo
	 */
	public readonly \App\Bo\AbstractBo $bo;

	/**
	 * Request parameters all merged (GET, POST).
	 * @var array
	 */
	protected array $parameters;

	/**
	 * Aspects list to execute.
	 * @var array
	 */
	protected array $actions;

	/**
	 * Request form data: define all request parameters.
	 * @var array
	 */
	protected ?array $formData = null;
	
	/**
	 * Authentication data.
	 * @var array
	 */
	protected ?array $sessionData = null;
	
	/**
	 * Data sources contain connection data and data source instance if used in $this->dataSourceAutoCommits.
	 * @var array
	 * @see /config/data-sources.<$_SERVER['SERVER_NAME']>.php
	 */
	protected array $dataSources;

	/**
	 * Data source auto commit statuses. Key is the data source name, and value indicates the auto commit status (true or false).
	 * @var array
	 */
	protected array $dataSourceAutoCommits = [];

	/**
	 * Logger.
	 * @var Psr\Log\LoggerInterface
	 */
	protected readonly LoggerInterface $logger;
	
	/**
	 * (non-PHPdoc)
	 * @see Symfony\Bundle\FrameworkBundle\Controller\AbstractController::__construct
	 */
	public function __construct(RequestStack $requestStack, LoggerInterface $logger)
	{
		// AbstractController has no constructor defined
		//parent::__construct();
		
		// Initialize apifony environment variables
		require $_SERVER['DOCUMENT_ROOT'] . '/../config/apifony/environment.php';

		// Retrieve data sources
		try {
			$this->dataSources = require $_ENV['CONFIG_ROOT'] . '/data-sources.' . $_SERVER['SERVER_NAME'] . '.php';
		} catch(\Throwable $e) {
			throw new \App\Exception\ConfigurationException('Configuration impossible: ' . $_ENV['CONFIG_ROOT'] . '/data-sources.' . $_SERVER['SERVER_NAME'] . '.php - ' . $e->getMessage());
		}
		
		// Set attributes
		$this->request = $requestStack->getCurrentRequest();
		$this->logger = $logger;
		
		// Retrieve request parameters
		$routeParams = $this->request->attributes->get('_route_params');
		unset($routeParams['_uri_']);
		$this->parameters = $_REQUEST;
		foreach($routeParams as $key => &$value) {
			$this->parameters[$key] = $value;
		}
		//$this->parameters = array_merge($_REQUEST, $routeParams);
		
		// Case $_SERVER['REDIRECT_URL'] does not exist
		if(!isset($_SERVER['REDIRECT_URL'])) {
			// Set REDIRECT_URL
			$_SERVER['REDIRECT_URL'] = explode('?', $_SERVER['REQUEST_URI'])[0];
		}

		// Case index ending by "/"
		if($_SERVER['REDIRECT_URL'][-1] === '/') {
			// Set index action by default
			$_SERVER['REDIRECT_URL'] .= 'index';
		}
	}

	/**
	 * Convert a string to camel case.
	 * @param string $string String to convert to camel case.
	 * @return string
	 */
	static public function toCamelCase(string $string): string {
		return lcfirst(str_replace(' ', '', ucwords(str_replace(array('_', '-'), ' ', $string))));
	}

	/**
	 * Convert a string to pascal case.
	 * @param string $string String to convert to pascal case.
	 * @return string
	 */
	static public function toPascalCase(string $string): string {
		return str_replace(' ', '', ucwords(str_replace(array('_', '-'), ' ', $string)));
	}
	
	/**
	 * Return request form data.
	 * @return null|array
	 */
	public function getFormData(): ?array
	{
		return $this->formData;
	}

	/**
	 * Return request parameters.
	 * @return array
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	/**
	 * Authenticate the client by IP using /config/clients.php. If failure, then AuthenticationException is thrown.
	 * @return void
	 * @throws \App\Exception\AuthenticationException
	 */
	public function authenticate(): void
	{
		// Case username or password missing
		if(!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
			throw new \App\Exception\AuthenticationException('Username or password missing.');
		}

		// Load private clients list
		$clients = require $_ENV['CONFIG_ROOT'] . '/clients.php';

		// Check client IP
		$ip = \App\Service\LogManager::getIp();
		if(!isset($clients[$ip])) {
			throw new \App\Exception\AuthenticationException('Access not allowed.');
		}

		// Check username and password
		if($clients[$ip]['username'] !== $_SERVER['PHP_AUTH_USER'] || $clients[$ip]['password'] !== $_SERVER['PHP_AUTH_PW']) {
			throw new \App\Exception\AuthenticationException('Username or password incorrect.');
		}
	}
	
	/**
	 * Check access rights to a service using /config/permissions.php. If access is not permitted, then PermissionException is thrown.
	 * @return void
	 * @throws \App\Exception\ForbiddenException
	 */
	public function checkServicePermission(): void
	{
		// Load clients permissions list by IP
		$ip = \App\Service\LogManager::getIp();
		$fileName = $_ENV['CONFIG_ROOT'] . '/permissions/' . $ip . '.php';
		if(!is_file($fileName)) {
			throw new \App\Exception\ForbiddenException('Permission not found on IP ' . $ip . '.');
		}
		$clients = require $fileName;

		// Check username in the clients list
		if(!isset($clients[$_SERVER['PHP_AUTH_USER']]) || empty($clients[$_SERVER['PHP_AUTH_USER']]['groups'])) {
			throw new \App\Exception\ForbiddenException('Permission not found on IP/username: ' . $ip . '/' . $_SERVER['PHP_AUTH_USER'] . '.');
		}

		// Check groups permissions
		$groups = &$clients[$_SERVER['PHP_AUTH_USER']]['groups'];
		$isAllowed = false;
		foreach($groups as &$groupName) {
			$fileName = $_ENV['CONFIG_ROOT'] . '/groups/' . $groupName . '.php';
			if(!is_file($fileName)) {
				throw new \App\Exception\ForbiddenException('Group not found: ' . $groupName . '.');
			}

			$permissions = require $fileName;
			foreach($permissions as &$permissionData) {
				if($permissionData['type'] === 'text') {
					if($permissionData['name'] === $_SERVER['REDIRECT_URL']) {
						$isAllowed = true;
						break;
					}
				}
				elseif($permissionData['type'] === 'regExp') {
					if(preg_match($permissionData['name'], $_SERVER['REDIRECT_URL'])) {
						$isAllowed = true;
						break;
					}
				}
				else {
					throw new \App\Exception\ForbiddenException('Permission type unknown: ' . $permissionData['type'] . '.');
				}
			}
			if($isAllowed) {
				break;
			}
		}
		if(!$isAllowed) {
			throw new \App\Exception\ForbiddenException('Service forbidden: ' . $_SERVER['REDIRECT_URL'] . '.');
		}
	}
	
	/**
	 * Traque le client en ajoutant un enregistrement dans la table adm_tracking.
	 * @param \Exception $exception (optionnel) Exception en cas d'erreur. null par défaut.
	 * @return void
	 */
	/*public function track(?\Throwable $exception = null): void
	{
		// Cas absence d'authentification et (administration ou test d'intégration ou planificateur de tâches ou trigger)
		if($this->sessionData === null || (isset($_SERVER['HTTP_USER_AGENT']) && ($_SERVER['HTTP_USER_AGENT'] == self::INTEGRATION_TEST_CLIENT))) { // || $_SERVER['HTTP_USER_AGENT'] == self::SCHEDULER_CLIENT || $_SERVER['HTTP_USER_AGENT'] == self::TRIGGER_CLIENT))) {
			// Ne trace pas
			return;
		}
		
		// Cas exception
		if($exception !== null) {
			$code = $exception->getCode();
			$message = $exception->getMessage();
		}
		
		// Cas pas d'erreur
		else {
			$code = 200;
			$message = 'OK';
		}

		//Récupère l'id du service appelé
		$service_id = isset($this->request->serviceId) ? $this->request->serviceId : null;
		if($service_id === null) {
			$pk = ['uri' => rtrim(explode('?', $_SERVER['REQUEST_URI'])[0], '/')];
			$service = $this->middleware->get('adm_service', $pk);
			if($service === null) {
				throw new \App\Exception\NotImplementedException('Service not implemented, tracking impossible: ' . $pk['uri']);
			}
			$service_id = $service['service_id'];
		}
		
		// Ajoute le tracking du client
		$this->middleware->insert(
			'adm_tracking',
			[
				'client_id' => isset($this->sessionData['client_id']) ? $this->sessionData['client_id'] : (isset($this->sessionData['administrator_id']) ? 1 : 0),
				'uri' => &$_SERVER['REQUEST_URI'],
				'data' => json_encode($this->parameters, \JSON_UNESCAPED_SLASHES),
				'service_id' => $service_id,
				'duration' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
				'code' => &$code,
				'message' => &$message,
				'created_date' => date('Y-m-d'),
				'created_time' => date('H:i:s')
			]
		);
	}*/
	
	/**
	 * Traque la réponse de la source de données, en ajoutant un enregistrement dans la table adm_tracking_<belair|datamart|owlink>.
	 * @param string $dataSourceName Nom de la source de données : belair, datamart ou owlink.
	 * @param string $method Nom de la méthode appelée (pour Belair) ou URL du web service (pour Datamart et Owlink).
	 * @param array $arguments Paramètres.
	 * @param float $timeStart Le microtime(true) mémorisé avant l'exécution du service.
	 * @param \Exception $exception (optionnel) Exception en cas d'erreur. null par défaut.
	 * @return void
	 */
	/*public function trackDataSource(string $dataSourceName, string $method, array $arguments, float $timeStart, ?\Throwable $exception = null): void
	{
		// Cas source de données inconnue
		if(!($dataSourceName == 'belair' || $dataSourceName == 'datamart' || $dataSourceName == 'owlink' || $dataSourceName == 'middleware')) {
			throw new \App\Exception\NotImplementedException('Service not implemented for service: ' . $dataSourceName);
		}
		
		// Cas exception
		if($exception !== null) {
			$code = $exception->getCode();
			$message = $exception->getMessage();
		}
		
		// Cas pas d'erreur
		else {
			$code = 200;
			$message = 'OK';
		}
		
		//Récupère l'id du service appelé
		$service_id = isset($this->request->serviceId) ? $this->request->serviceId : null;
		if($service_id === null) {
			$pk = ['uri' => rtrim(explode('?', $_SERVER['REQUEST_URI'])[0], '/')];
			$service = $this->middleware->get('adm_service', $pk);
			if($service === null) {
				throw new \App\Exception\NotImplementedException('Service unknown: ' . $pk['uri']);
			}
			$service_id = $service['service_id'];
		}

		// Ajoute le tracking du client
		$this->middleware->insert(
			'adm_tracking_' . $dataSourceName,
			[
				'client_id' => isset($this->sessionData['client_id']) ? $this->sessionData['client_id'] : 1,
				'uri' => &$method,
				'data' => json_encode($arguments, \JSON_UNESCAPED_SLASHES),
				'service_id' => $service_id,
				'duration' => microtime(true) - $timeStart,
				'code' => &$code,
				'message' => &$message,
				'created_date' => date('Y-m-d'),
				'created_time' => date('H:i:s')
			]
		);
	}*/

	/*public function getParameters(): array 
	{
		return $this->parameters;
	}*/
	
	/**
	 * (non-PHPdoc)
	 * @see Symfony\Bundle\FrameworkBundle\Controller\AbstractController::json
	 */
    protected function json($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
		// Cas particulier
		if($data === null || $data === [] || (isset($data['rows']) && $data['rows'] === [])) {
			throw new \App\Exception\NotFoundException('Resource not found.');
		}
		
		// Ajoute des informations complémentaires et encapsule la réponse dans data
		return new JsonResponse(
			json_encode([
				'uri' => &$_SERVER['REDIRECT_URL'],
				'parameters' => empty($this->parameters) ? null : $this->parameters,
				'method' => &$_SERVER['REQUEST_METHOD'],
				'duration' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
				'created' => date('Y-m-d H:i:s'),
				'data' => is_string($data) && $data[0] == '{' ? json_decode($data, true) : $data
			], \JSON_UNESCAPED_SLASHES),
			$status,
			$headers,
			true
		);
    }
	
	/**
	 * Retourne la réponse de contenu XML à partir d'un tableau associatif.
	 * @param array $data Données sous forme de tableau associatif.
	 * @param int $status (optionnel) Code HTTP. 200 pour OK par défaut.
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \App\Exception\NotFoundException
	 */
	protected function xml(array $data, int $status = 200): Response
    {
		// Cas particulier
		if($data === null || $data === [] || (isset($data['rows']) && $data['rows'] === [])) {
			throw new \App\Exception\NotFoundException('Resource not found.');
		}
		
		// Construit le XML
		$data = [
			'uri' => $_SERVER['REDIRECT_URL'],
			'parameters' => empty($this->parameters) ? null : $this->parameters,
			'method' => $_SERVER['REQUEST_METHOD'],
			'duration' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
			'created' => date('Y-m-d H:i:s'),
			'data' => $data
		];
		
		// Construit la réponse
		$response = new Response((new \App\Service\Xml($data))->saveXML());
		$response->headers->set('Content-Type', 'text/xml');
		$response->setStatusCode($status);
		
		// Retourne la réponse
		return $response;
    }
	
	/**
	 * Annule la transction si elle existe, puis reconstruit l'exception au format de réponse Middleware si besoin.
	 * @param \Throwable $e Exception.
	 * @return \Throwable
	 */
	/*public function toException(\Throwable $e): \Throwable
	{
		// Annule la transaction
		if($this->isTransaction) {
			try {
				$this->middleware->rollBack();
			} catch(\Exception $ex) {
				// Logue l'erreur technique en base de données ou dans le dossier /log/
				\App\Service\LogManager::log($ex, $this->middleware);
			}
		}
		
		// Cas d'erreur connue : colonne inconnue
		// 42703 pour PostgreSQL
		// 42S22 pour MSSQL
		// 42000 pour PostgreSQL, hors indexe/numéro de colonne ('/^Internal Server Error : SQLSTATE\[42000\].* ORDER BY .*$/')
		$code = $e->getCode();
		if($code == '42703' || $code == '42S22') {
			
			if(!isset($_SERVER['HTTP_USER_AGENT']) || ($_SERVER['HTTP_USER_AGENT'] !== self::INTEGRATION_TEST_CLIENT && $_SERVER['HTTP_USER_AGENT'] !== self::SCHEDULER_CLIENT)) {
				// Logue l'erreur technique en base de données ou dans le dossier /log/
				\App\Service\LogManager::log($e, $this->middleware);
			}
				
			// Crée l'exception
			$re = [
				'42703' => '/(« |")([^\s]+)( »|")/',
				'42S22' => '/\'([^\s\']+)\'\.$/'
			];
			$matches = null;
			preg_match($re[$code], $e->getMessage(), $matches);
			$e = new \App\Exception\BadRequestException('Undefined column: ' . $matches[0]);
		}
		
		// Cas hors indexe/numéro de colonne : SQLSTATE[42P10]: Invalid column reference: 7 ERROR: ORDER BY position 123 is not in select list LINE 1: SELECT * FROM type_courtier ORDER BY 123 OFFSET $1 LIMIT $2
		// 42P10 pour PostgreSQL
		elseif($code == '42P10') {
			// Récupère le champ invalide
			$matches = null;
			preg_match('/ position ([^\s,]+)/', $e->getMessage(), $matches);
			
			// Crée l'exception
			$e = isset($this->parameters['sortBy']) && $this->parameters['sortBy'] == $matches[1]
				? new \App\Exception\FormDataException(json_encode(['fieldId' => 'sortBy', 'type' => 'format', 'message' => &$this->formData['sortBy']['format']['message'], 'label' => &$this->formData['sortBy']['label']], \JSON_UNESCAPED_SLASHES))
				: new \App\Exception\BadRequestException('Invalid column reference: ' . $matches[1]);
		}
		
		// Cas erreur de dépence à la suppression ou modification d'une ligne en table
		// 23503 pour PostgreSQL
		elseif($code == '23503') {	
			// Crée l'exception
			$e = new \App\Exception\BadRequestException(explode('ERREUR:', $e->getMessage())[1]);
		}

		// Cas demande expirée
		elseif(preg_match('/timed out|time[\x20-]?out/', $e->getMessage()) === 1) {
			$e = new \App\Exception\RequestTimeoutException($e->getMessage());
			\App\Service\LogManager::log($e, $this->middleware);
		}

		// Cas erreur technique
		else {
			$code = (int)$code;
			if($code < 100 || $code >= 500) {
				// Logue l'erreur technique en base de données ou dans le dossier /log/
				\App\Service\LogManager::log($e, $this->middleware);

				// Cas code d'erreur incorrect, car il doit être composé de 3 chiffres
				if($code < 100 || $code > 527) {
					$e = '\\' . get_class($e);
					$e = new $e('Internal Server Error', 500);
				}
			}
		}
		
		// Retourne la nouvelle exception
		return $e;
	}*/
	
	/**
	 * Contrôle les données de formulaire ou des paramètres de la requête. Si une donnée/paramètre ne respecte pas une condition de validation, alors l'exception \App\Exception\FormDataException est levée.
	 * @param array $data (optionnel) Données à contrôler. $this->parameters par défaut.
	 * @param array $formData (optionnel) Description des données de formulaire ou des paramètres de la requête. $this->formData par défaut.
	 * @return void
	 * @throws \App\Exception\FormDataException
	 */
	public function checkParameters(array &$data = null, array &$formData = null): void
	{
		//Form data is required
		if($formData === null) {
			$formData = &$this->formData;
			if($formData === null) {
				return;
			}
		}
		
		//Set data by default
		if($data === null) {
			$data = &$this->parameters;
		}

		//Loop on form data
		foreach($formData as $key => &$fieldData) {
			//Case title exists
			//if(isset($fieldData['title'])) {
			//	$fieldData['label'] = &$fieldData['title'];
			//}
			$label = isset($fieldData['title']) ? $fieldData['title'] : $fieldData['label'];

			//Case data exists in formData
			if(isset($data[$key])) {
				//Case array value from select, checkbox
				//Case string value from input, textarea, radio
				//$values = is_array($data[$key]) ? $data[$key] : [$data[$key]];
				if(is_array($data[$key])) {
					//Check required value
					if($fieldData['required']['value'] && empty($data[$key])) {
						//Case no default value
						if($fieldData['defaultValue'] === null) {
							throw new \App\Exception\FormDataException(json_encode(['fieldId' => &$key, 'type' => 'required', 'message' => &$fieldData['required']['message'], 'label' => &$label], \JSON_UNESCAPED_SLASHES));
						}

						//Set default value
						$data[$key] = is_array($fieldData['defaultValue']) ? $fieldData['defaultValue'] : [$fieldData['defaultValue']];
					}

					//Set values to check
					$values = $data[$key];
				}
				else {
					//Put value into values list to check
					$values = [&$data[$key]];
				}
				
				//Loop on values
				foreach($values as &$value) {
					//Clean value: string by default
					$value = trim($value);
					
					//Check required value
					if($fieldData['required']['value'] && $value === '') {
						//Case no default value
						if($fieldData['defaultValue'] === null) {
							throw new \App\Exception\FormDataException(json_encode(['fieldId' => &$key, 'type' => 'required', 'message' => &$fieldData['required']['message'], 'label' => &$label], \JSON_UNESCAPED_SLASHES));
						}

						//Set default value
						$data[$key] = $value = $fieldData['defaultValue'];
					}
					
					//Check length and format
					$length = \mb_strlen($value, 'UTF-8');
					if($length > 0) {
						//Check miunimum length
						if($fieldData['minLength']['value'] && $length < $fieldData['minLength']['value']) {
							throw new \App\Exception\FormDataException(json_encode(['fieldId' => &$key, 'type' => 'minLength', 'message' => &$fieldData['minLength']['message'], 'label' => &$label], \JSON_UNESCAPED_SLASHES));
						}
						
						//Check maximum length
						if($fieldData['maxLength']['value'] && $length > $fieldData['maxLength']['value']) {
							throw new \App\Exception\FormDataException(json_encode(['fieldId' => &$key, 'type' => 'maxLength', 'message' => &$fieldData['maxLength']['message'], 'label' => &$label], \JSON_UNESCAPED_SLASHES));
						}
						
						//Check format
						if($fieldData['format']['value']) {
							//Case filter with constant
							if(is_int($fieldData['format']['value'])) {
								if(!filter_var($value, $fieldData['format']['value'])) {
									throw new \App\Exception\FormDataException(json_encode(['fieldId' => &$key, 'type' => 'format', 'message' => &$fieldData['format']['message'], 'label' => &$label], \JSON_UNESCAPED_SLASHES));
								}
							}

							//Case filter with string
							elseif($fieldData['format']['value'][0] !== '/') {
								//Examples: 'EMAIL' or 'FILTER_VALIDATE_EMAIL'
								$c = strtoupper($fieldData['format']['value']);
								$filter = 'FILTER_VALIDATE_' . $c;
								if(!defined($filter) || !filter_var($value, constant($filter))) {
									if(!defined($c) || !filter_var($value, constant($c))) {
										throw new \App\Exception\FormDataException(json_encode(['fieldId' => &$key, 'type' => 'format', 'message' => &$fieldData['format']['message'], 'label' => &$label], \JSON_UNESCAPED_SLASHES));
									}
								}
							}

							//Case regular expression
							elseif(!preg_match($fieldData['format']['value'], $value)) {
								throw new \App\Exception\FormDataException(json_encode(['fieldId' => &$key, 'type' => 'format', 'message' => &$fieldData['format']['message'], 'label' => &$label], \JSON_UNESCAPED_SLASHES));
							}
						}
						
						//Check select: $fieldData['values'] should not be empty
						if($fieldData['type'] === 'select' && $fieldData['values'] !== null) {
							//Case select key not found with $value string type by default
							if(!isset($fieldData['values'][$value])) {
								//Get key type: the first can be empty, so get the second
								//$keys = array_keys($fieldData['values']);
								//$type = gettype($keys[1]);
								$type = gettype(key($fieldData['values']));

								//String type is already checked by default
								if($type === 'string') {
									throw new \App\Exception\FormDataException(json_encode(['fieldId' => &$key, 'type' => 'format', 'message' => &$fieldData['format']['message'], 'label' => &$label], \JSON_UNESCAPED_SLASHES));
								}

								//Check with key type
								$castedValue = $value;
								settype($castedValue, $type);
								if(!isset($fieldData['values'][$castedValue])) {
									throw new \App\Exception\FormDataException(json_encode(['fieldId' => &$key, 'type' => 'format', 'message' => &$fieldData['format']['message'], 'label' => &$label], \JSON_UNESCAPED_SLASHES));
								}
							}
						}
						
						//Check number: min and max
						elseif($fieldData['type'] === 'number') {
							//Cast value to integer or decimal: integer by default
							$data[$key] = $value = !isset($fieldData['step']) || is_int($fieldData['step']) ? (integer)$value : (float)$value;

							//Case min exists
							if(isset($fieldData['min']) && $value < $fieldData['min']) {
								throw new \App\Exception\FormDataException(json_encode(['fieldId' => &$key, 'type' => 'format', 'message' => &$fieldData['format']['message'], 'label' => &$label], \JSON_UNESCAPED_SLASHES));
							}
							
							//Case max exists
							if(isset($fieldData['max']) && $value > $fieldData['max']) {
								throw new \App\Exception\FormDataException(json_encode(['fieldId' => &$key, 'type' => 'format', 'message' => &$fieldData['format']['message'], 'label' => &$label], \JSON_UNESCAPED_SLASHES));
							}
						}
					}
				}
			}
			
			//Case data is not set
			else {
				$data[$key] = null;
			}

			//Case data is not set or empty
			if($data[$key] === null || $data[$key] === '') {
				//Set default value
				$data[$key] = $fieldData['defaultValue'];
				
				//Case required parameter
				if($fieldData['required']['value']) {
					//Case no default value
					if($fieldData['type'] === 'checkbox' || $fieldData['defaultValue'] === null) {
						throw new \App\Exception\FormDataException(json_encode(['fieldId' => &$key, 'type' => 'required', 'message' => &$fieldData['required']['message'], 'label' => &$label], \JSON_UNESCAPED_SLASHES));
					}
				}
			}
		}
	}
	
	/**
	 * Use CURL to send synchronous request.
	 * @param string $url URL.
	 * @param array|string $parameters (optional) Parameters with key/value. null by default.
	 * @param string $method (optional) Request method. "GET" method by default.
	 * @param array $options (optional) CURL options. null by default.
	 * @param int $httpCode (option) HTTP status code to retrieve. 0 by default.
	 * @param int $loop Maximum of loops on expired request. 3 by default.
	 * @param int $usleep Maximum duration of sleep in micro second before each loop. 3000000 µs (3000 ms = 3 s) by default.
	 * @return string Request reponse.
	 * @throws \Exception
	 */
	/*static public function send(string $url, array|string $parameters = null, string $method = 'GET', array $options = null, int &$httpCode = 0, int $loop = 3, int $usleep = 3000000): string
	{
		//Build query string
		$queryString = $parameters != null ? (is_array($parameters) ? http_build_query($parameters) : $parameters) : null;
		
		//Case GET method
		$isGetMethod = $method == 'GET';
		if($isGetMethod && $queryString !== null) {
			$t = explode('?', $url);
			$url = $t[0] . '?' . (isset($t[1]) ? $t[1] . '&' . $queryString : $queryString);
		}
		
		//Define default options: these options can be overwritten by $options
		$defaultOptions = [
			CURLOPT_URL => &$url,
			CURLOPT_POSTFIELDS => &$queryString,
			CURLOPT_POST => !$isGetMethod,
			CURLOPT_CUSTOMREQUEST => &$method,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_FRESH_CONNECT => true,

			CURLOPT_HEADER => false,
			//CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17',

			// Fix Error 51: SSL: certificate subject name xxx does not match target host name
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			
			// Fix OpenSSL SSL_connect: SSL_ERROR_SYSCALL in connection to middleware-dev.courtage.apivia.fr:443
			CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2, //6

			CURLOPT_TIMEOUT => 30
		];
		
		//Case options defined: add and/or overwrite options
		if($options !== null) {
			//Add options to default options
			foreach($options as $key => &$value) {
				$defaultOptions[$key] = &$value;
			}
		}
		
		//Initialize CURL
		$ch = curl_init();
		try {
			//Set options
			if(curl_setopt_array($ch, $defaultOptions) === false) {
				throw new \Exception(curl_error($ch));
			}
			
			do {
				//Send request and retrieve response
				$response = curl_exec($ch);
				if($response !== false) {
					break;
				}
				
				//Case no response, sleep before the next request
				if(--$loop > 0) {
					//Random pause
					usleep(mt_rand(0, $usleep));
				}
			} while($loop > 0);
			
			//Retrieve HTTP code
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			
			//Case error
			if($response === false) {
				throw new \Exception(curl_error($ch));
			}
		//} catch(\Exception $e) {
		//	throw $e;
		} finally {
			//End cURL
			curl_close($ch);
		}
		
		//Return request response
		return $response;
	}*/

	/**
	 * Send an asynchronous request.
	 * @param string $url URL.
	 * @param array $parameters Parameters. null by default.
	 * @param string $method (optional) Request method. "GET" method by default.
	 * @param array $options (optional) CURL options. null by default. Example: ['Authorization' => 'Basic ' . base64_encode($username . ':' . $password)]
	 * @param array $cookies Cookies with key/value. null by default. Example: ['PHPSESSID' => session_id(), 'path' => '/', 'id' => 3]
	 * @return bool true if sent, otherwise false.
	 */
	/*static public function sendAsync(string $url, array $parameters = null, string $method = 'GET', array $options = null, array $cookies = null): bool
	{
		try {
			//Parse URL
			$crlf = "\r\n";
			$parsedUrl = parse_url($url);
			
			//Initialize scheme and port
			if(isset($parsedUrl['scheme']) && $parsedUrl['scheme'] === 'https') {
				$parsedUrl['scheme'] = 'ssl://';
				$parsedUrl['port'] = 443;
			}
			else {
				$parsedUrl['scheme'] = '';
				if(!isset($parsedUrl['port'])) {
					$parsedUrl['port'] = 80;
				}
			}
			
			//Define host
			if(!isset($parsedUrl['host'])) {
				$t = explode('/', $parsedUrl['path']);
				$parsedUrl['host'] = $t[0];

				unset($t[0]);
				$parsedUrl['path'] = '/' . implode('/', $t);
			}
			
			//Build cookies
			if($cookies != null) {
				foreach($cookies as $key => &$value) {
					$value = $key . '=' . $value;
				}
				$cookies = 'Cookie: ' . implode('; ', $cookies) . $crlf;
			}
			else {
				$cookies = '';
			}
			
			//Prepare data to send
			$query = !empty($parameters) ? http_build_query($parameters) : null;
			
			//Case GET method
			$isGetMethod = $method == 'GET';
			if($isGetMethod) {
				//Retrieve query string
				if(isset($parsedUrl['query'])) {
					$query = $query === null ? $parsedUrl['query'] : $query . '&' . $parsedUrl['query'];
				}
				
				//Build request
				$query = 'GET ' . $parsedUrl['path'] . ($query != null ? '?' . $query : '') . ' HTTP/1.1' . $crlf
					. 'Host: ' . $parsedUrl['host'] . $crlf
					. $cookies
					. 'Connection: Close' . $crlf
					. $crlf;
			}
			
			//Case other method
			else {
				//Initialize request
				$query = $method . ' ' . $parsedUrl['path'] . ' HTTP/1.1' . $crlf
					. 'Host: ' . $parsedUrl['host'] . $crlf
					. $cookies;
				
				//Case customize request
				if($options !== null) {
					foreach($options as $key => &$value) {
						$query .= $key . ': ' . $value . $crlf;
					}
				}
				
				//Case content type is not set yet
				if(!isset($options['Content-Type'])) {
					//Inform content type
					$query .= 'Content-Type: application/x-www-form-urlencoded' . $crlf;
				}
				
				//Finalize request
				$query .= 'Content-Length: ' . strlen($query) . $crlf
					. 'Connection: Close' . $crlf
					. $crlf
					. $query;
			}
			
			//Set stream context
			$streamContext = stream_context_create(
				[
					'ssl' => [
						'verify_peer' => false,
						'verify_peer_name' => false
					]
				]
			);
			
			//Open socket connection
			$errno = null;
			$errstr = null;
			if(($fp = \stream_socket_client($parsedUrl['scheme'] . $parsedUrl['host'] . ':' . $parsedUrl['port'], $errno, $errstr, ini_get('default_socket_timeout'), STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT, $streamContext)) === false) {
				throw new \Exception(__METHOD__ . ' stream_socket_client error ' . $errno . ' ' . $errstr . ': ' . $url . "\nParameters: " . \print_r($parameters, true) . "\n" . $query);
			}
			
			//Allow asynchronous
			ignore_user_abort(true);
			stream_set_blocking($fp, false);

			//Send request
			if(fwrite($fp, $query) === false) {
				throw new \Exception(__METHOD__ . ' fwrite error' . ': ' . $url . "\nParameters: ". \print_r($parameters, true) . "\n" . $query);
			}
			
			//Case not GET method
			if(!$isGetMethod) {
				//Pause 20ms to be sure the query is sent before closing
				usleep(20000);
			}
			
			//Close socket connection
			if(fclose($fp) === false) {
				throw new \Exception(__METHOD__ . ' fclose error' . ': ' . $url . "\nParameters: ". \print_r($parameters, true) . "\n" . $query);
			}
		} catch(\Exception $e) {
			//Log error but not block the process
			\App\Service\LogManager::log($e, $this->middleware);
			return false;
		}

		return true;
	}*/

	/**
	 * Launch asynchronous requests, then return response of each request in an array.
	 * @param array $curls Collection of curls options.
	 * @return array
	 */
	/*static public function sendMultiple(array &$curls): array
	{
		//Initialize handlers manager
		$multihandler = curl_multi_init();

		//Add handles to handles manager
		$handlers = [];
		foreach($curls as $curlKey => &$options) {
			//Set URL
			$handlers[$curlKey] = curl_init($options[CURLOPT_URL]);
			
			//Remove URL from options
			unset($options[CURLOPT_URL]);
			
			//Set options (without CURLOPT_URL option)
			foreach($options as $key => &$value) {
				curl_setopt($handlers[$curlKey], $key, $value);
			}
			
			//Set return request response by default
			if(!isset($options[CURLOPT_RETURNTRANSFER])) {
				curl_setopt($handlers[$curlKey], CURLOPT_RETURNTRANSFER, true);
			}
			
			//Add handle to handles manager
			curl_multi_add_handle($multihandler, $handlers[$curlKey]);
		}

		//Launch requests
		$pendingConnex = 0;
		do {
			curl_multi_exec($multihandler, $pendingConnex);
			usleep(10000); // 10 ms
		} while($pendingConnex > 0);

		//Retrieve requests response
		$results = [];
		foreach($handlers as $curlKey => &$handle) {
			//Retrieve response request
			$results[$curlKey] = curl_multi_getcontent($handle);

			//Close handle
			curl_multi_remove_handle($multihandler, $handle);
		}

		//Close handles manage
		curl_multi_close($multihandler);

		// Retourne les résultats des requêtes
		return $results;
	}*/
	
	/**
	 * Retourne le message d'erreur du champ au format JSON.
	 * @param string $fieldId Identifiant du champ dans le form data.
	 * @param string $type Type d'erreur : format, required, minLength, maxLength, messages. "format" par défaut.
	 * @param string $key Clé de l'erreur pour le type "messages" uniquement.
	 * @return string
	 */
	/*public function getFieldMessage(string $fieldId, string $type = 'format', string $key = null): string
	{
		// Cas champ introuvable
		if(!isset($this->formData[$fieldId])) {
			return '{"fieldId": "", "type": "", "message": "", "label": ""}';
		}
		
		// Récupère les données du champ
		$field = &$this->formData[$fieldId];

		// Retourne le message du champ
		return json_encode([
			'fieldId' => $fieldId,
			'type' => $type,
			'message' => &$field[$type][$type != 'messages' ? 'message' : $key],
			'label' => &$field['label']
		], \JSON_UNESCAPED_SLASHES);
	}*/
	
	/**
	 * Encode une chaîne de caractères en base 64.
	 * @param string $string Chaîne de caractères à encoder en base 64.
	 * @return string
	 */
	/*static public function base64url_encode(string $string): string
	{
		return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
	}*/
	
	/**
	 * Décode une chaîne de caractères en base 64.
	 * @param string $string Chaîne de caractères en base 64 à décoder.
	 * @return string
	 */
	/*static public function base64url_decode(string $string): string
	{
		return base64_decode(strtr($string, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($string)) % 4));
	}*/
	
	/**
	 * Contrôle une date au format jj/mm/aaaa ou jj-mm-aaaa.
	 * @param string $date Date.
	 * @return bool
	 */
	/*static public function isDmy(string $date) : bool
	{
		$matches = null;
		return preg_match('/^(\d\d)[\/-](\d\d)[\/-](\d{4})$/', $date, $matches) ? checkdate((int)$matches[2], (int)$matches[1], (int)$matches[3]) : false;
	}*/
	
	/**
	 * Convertit une date au format aaaa-mm-jj.
	 * @param string $date Date à convertir.
	 * @return string
	 */
	/*static public function toYmd(string $date): string
	{
		return preg_replace('/^(\d\d)[\/-](\d\d)[\/-](\d{4})$/', '$3-$2-1', $date);
	}*/
	
	/**
	 * Retourne une chaîne de caractères en nom de méthode (ex : get-rows en getRows).
	 * @param string $string Chaîne de caractères (ex : get-rows).
	 * @return string
	 */
	/*static public function toMethodName(string $string): string
	{
		return str_replace(' ', '', ucwords(preg_replace('/[^a-zA-Z\d]+/', ' ', $string)));
	}*/
	
	/**
	 * Convert string to a valid URL in lower case.
	 * Accented characters are replaced by no accented characters equivalent, delimite upper case by "-", and non alphanumeric characters are replaced by "-" (hyphen).
	 * 
	 * @param string $string String.
	 * 
	 * @return string Valide Url.
	 */
	/*static public function toUrl(string $string): string {
		//Optimize process
		return trim(preg_replace('/([^a-z0-9]+)/', '-', html_entity_decode(preg_replace('/&(.)(acute|cedil|circ|grave|ring|tilde|uml);/', '$1', htmlentities(mb_strtolower(preg_replace('/([A-Z])/', '-$1', $string), 'UTF-8'), ENT_COMPAT, 'UTF-8')), ENT_COMPAT, 'UTF-8')), " -\t\n\r\0\x0B");
	}*/
	
	/**
	 * Retourne les valeurs par défaut des paramètres d'une méthode. null est affecté par défaut aux paramètres obligatoires.
	 * 
	 * @param type $instance Instance de classe.
	 * @param string $methodeName Nom de méthode.
	 * 
	 * @return array
	 */
	/*static public function getDefaultParameterValues($instance, string $methodeName): array
	{
		// Récupère les paramètres de la méthode
		$parameters = (new \ReflectionMethod($instance, $methodeName))->getParameters();
		
		// Construit la liste des paramètres de la méthode
		$defaultParameters = [];
		foreach($parameters as &$reflectionParameter) {
			$defaultParameters[$reflectionParameter->name] = $reflectionParameter->isDefaultValueAvailable()
				? $reflectionParameter->getDefaultValue()
				: null;
		}
		
		// Retoure la liste des paramètres
		return $defaultParameters;
	}*/
	
	/**
	 * Crée ou modifie un fichier contenant l'affectation d'une variable PHP.
	 * @param string $fileName Nom du fichier à créer ou à modifier.
	 * @param string $variableName Nom de variable.
	 * @param mixed $variableValue Valeur de la variable.
	 * @return void
	 * @throws \App\Exception\FileException
	 */
	/*static public function setFilePhpVariableContent(string $fileName, string $variableName, $variableValue): void {
		// Cas tableau
		if(is_array($variableValue)) {
			// Convertit le tableau en chaîne de caractères
			$variableValue = preg_replace(
				['/array\x20*\(/', '/,([\n\r\t\x20]*)\)/', '/\d+=>(\d+)/'],
				['[', '$1]', '$1'],
				var_export($variableValue, true)
			);
		}
		
		// Cas chaîne de caractères
		elseif(is_string($variableValue)) {
			// Ajoute le délimiteur de chaîne de caractères
			$variableValue = '\'' . $variableValue . '\'';
		}
		
		// Crée ou modifie le fichier
		if(false === file_put_contents($fileName, '<?php ' . '$' . $variableName . ' = ' . $variableValue . ';?>')) {
			throw new \App\Exception\FileException('Write file impossible: ' . $fileName);
		}
	}*/

	/**
	 * Encrypt a string.
	 * @param string $string String to encrypt.
	 * @return string
	 */
	static public function encrypt(string $string): string
	{
		// @todo
		return $string;
	}

	/**
	 * Decrypt an encrypted string.
	 * @param string $string Encrypted string to decrypt.
	 * @return string
	 */
	static public function decrypt(string $string): string
	{
		// @todo
		return $string;
	}

	public function addDataSource(string $dataSourceName, object &$instance): void
	{
		// Store data source instance
		$this->dataSources[$dataSourceName] = &$instance;

		// Disable auto commit to allow transaction mode
		$this->dataSourceAutoCommits[$dataSourceName] = true;
	}

	/**
	 * Begin transaction.
	 * @param string $dataSourceName Data source name.
	 * @return void
	 */
	protected function _beginTransaction(string $dataSourceName): void
	{
		// Case user name and password crypted
		$dataSource = &$this->dataSources[$dataSourceName];
		if($dataSource['crypted']) {
			// Decrypt userName and password
			$dataSource['userName'] = self::decrypt($dataSource['userName']);
			$dataSource['password'] = self::decrypt($dataSource['password']);
		}

		// Convert connection data to data source instance
		$dsClass = '\\App\\Service\\' . ucfirst($dataSource['type']) . 'Service';
		$dataSource['dataSourceName'] = $dataSourceName;
		try {
			$dataSource = new $dsClass($dataSource);
		} catch(\Throwable $e) {
			throw new \App\Exception\DataSourceException('Instanciate data source impossible: ' . $dsClass . ': ' . $dataSourceName . ' - ' . $e->getMessage());
		}

		// Begin transaction
		if(!($dataSource instanceof \Redis)) {
			// Begin transaction
			$dataSource->beginTransaction(true);
			$this->dataSourceAutoCommits[$dataSourceName] = false;
		}
	}

	/**
	 * Start transaction and disable auto-commit.
	 * @param string (optional) $dataSourceName Data source name. null by default to apply to all data sources.
	 * @return void
	 */
	public function beginTransaction(string $dataSourceName = null): void
	{
		if($dataSourceName !== null) {
			// Case data source not found
			if(!isset($this->dataSources[$dataSourceName])) {
				throw new \App\Exception\DataSourceException('Begin transaction with an unknown data source: ' . $dataSourceName . '.');
			}

			// Case connection data and not Redis
			if(is_array($this->dataSources[$dataSourceName]) && $this->dataSources[$dataSourceName]['type'] !== 'redis') {
				// Begin transaction
				$this->_beginTransaction($dataSourceName);
			}
		}
		else {
			foreach($this->dataSources as $dataSourceName => &$dataSource) {
				// Case connection data and not Redis
				if(is_array($dataSource) && $dataSource['type'] !== 'redis') {
					// Begin transaction
					$this->_beginTransaction($dataSourceName);
				}
			}
		}
	}

	/**
	 * Commit transaction.
	 * @param string (optional) $dataSourceName Data source name. null by default to apply to all data sources.
	 * @return void
	 */
	public function commit(string $dataSourceName = null): void
	{
		if($dataSourceName !== null) {
			// Case data source not found
			if(!isset($this->dataSources[$dataSourceName])) {
				throw new \App\Exception\DataSourceException('Commit transaction with an unknown data source: ' . $dataSourceName . '.');
			}

			if(!$this->dataSourceAutoCommits[$dataSourceName]) {
				$this->dataSources[$dataSourceName]->commit();
			}
		}
		else {
			foreach($this->dataSourceAutoCommits as $dataSourceName => &$isAutocommit) {
				if(!$isAutocommit) {
					$this->dataSources[$dataSourceName]->commit();
				}
			}	
		}
	}

	/**
	 * Rollback transaction.
	 * @param string (optional) $dataSourceName Data source name. null by default to apply to all data sources.
	 * @return void
	 */
	public function rollback(string $dataSourceName = null): void
	{
		if($dataSourceName !== null) {
			// Case data source not found
			if(!isset($this->dataSources[$dataSourceName])) {
				throw new \App\Exception\DataSourceException('Rollback transaction with an unknown data source: ' . $dataSourceName . '.');
			}

			if(!$this->dataSourceAutoCommits[$dataSourceName]) {
				$this->dataSources[$dataSourceName]->rollback();
			}
		}
		else {
			foreach($this->dataSourceAutoCommits as $dataSourceName => &$isAutocommit) {
				if(!$isAutocommit) {
					$this->dataSources[$dataSourceName]->rollback();
				}
			}	
		}
	}

	/**
	 * #[Route('/*', name: 'api_bo')]
	 * Run bo process
	 * @return void
	 * @uses https://xxx/*
	 */
    public function run(RequestStack $requestStack): Response
    {
		// Case no BO set (ex: /<weaver> instead of /<bo>/<weaver>)
		$names = explode('/', $_SERVER['REDIRECT_URL']);
		if(count($names) === 2) {
			// Set "Index" BO by default
			$names[0] = 'index';
		}
		else {
			// Remove the first empty element
			unset($names[0]);
		}

		// Determine response format
		$format = !isset($this->parameters['format']) || ($this->parameters['format'] != 'json' && $this->parameters['format'] != 'xml') ? 'json' : $this->parameters['format'];

		// Run aspects weaver
		$mainAction = array_pop($names);
		$response = null;
		try {
			// Check BO
			$name = array_pop($names);
			$path = $names === [] ? '/' : '/' . implode('/', $names) . '/';
			$BoName = self::toPascalCase($name) . $_ENV['BO_CLASS_SUFFIX'];
			$fileName = $_ENV['BO_ROOT'] . $path . $BoName . $_ENV['BO_FILE_EXTENSION'];
			if(!is_file($fileName)) {
				throw new \App\Exception\ServiceUnavailableException('Bo class not found for ' . $_SERVER['REDIRECT_URL'] . '. File expected: ' . $fileName);
			}

			// Load request form data
			$fileName = $_ENV['FORM_ROOT'] . $_SERVER['REDIRECT_URL'] . $_ENV['FORM_FILE_EXTENSION'];
			if(is_file($fileName)) {
				// Load fields
				$fields = require $fileName;

				// Build form data
				foreach($fields as &$fieldName) {
					$this->formData[$fieldName] = require $_ENV['FIELD_ROOT'] . '/' . $fieldName . $_ENV['FIELD_FILE_EXTENSION'];
				}
			}

			// Load request BO
			$BoName = '\\App\\Bo' . str_replace('/', '\\', $path) . $BoName;
			$this->bo = new $BoName($this, $this->dataSources, $this->formData, $this->parameters);
			
			// Load request weaver (actions list)
			$fileName = $_ENV['WEAVER_ROOT'] . $_SERVER['REDIRECT_URL'] . $_ENV['WEAVER_FILE_EXTENSION'];
			$this->actions = is_file($fileName) ? require $fileName : [
				[
					'instance' => &$this->bo,
					'method' => self::toCamelCase($mainAction),
					'arguments' => null,
					'callbackBefore' => null,
					'callbackAfter' => null
				]
			];

			// Run each aspect sequentially
			foreach($this->actions as &$actionData) {
				// Case callback before exists
				if($actionData['callbackBefore'] !== null) {
					$actionData['callbackBefore']();
				}

				// Execute aspect (action) and retrieve result
				$methodName = &$actionData['method'];
				$returnedValue = $actionData['arguments'] === null
					? $actionData['instance']->$methodName()
					: $actionData['instance']->$methodName(...$actionData['arguments']);

				// Case response not yet set
				if($response === null && $returnedValue instanceof Response) {
					// Set response
					$response = $returnedValue;
				}

				// Case callback after exists
				if($actionData['callbackAfter'] !== null) {
					$actionData['callbackAfter']();
				}
			}
			
			// Commit transaction
			$this->commit();
		} catch(\Throwable $e) {
			// Rollback transaction
			$this->rollback();

			// Case not App\Exception\xxxException
			$className = $e::class;
			$file = $e->getFile();
			$line = $e->getLine();
			if(!($e instanceof \App\Exception\AbstractException)) {
				$e = new \App\Exception\FrameworkException($e->getMessage());
			}
			
			// Build response
			$code = $e->getCode();
			$message = $code === 412 ? $e->logMessage : $e->getMessage();
			$response = $this->$format($message !== '' && $message[0] === '{' && $format == 'json' ? $message : ['message' => &$message], $code);
			
			// Log error
			$message = $className . ' ' . $e->logMessage . ' - Error in ' . $file . ' at line ' . $line .'.';
			if($code >= 500) {
				$this->logger->error($message);
			}
			elseif($code >= 400 && $code !== 412) {
				$this->logger->warning($message);
			}
			else {
				$this->logger->info($message);
			}
		}
		
		// Return response
		return $response === null ? $this->$format(['message' => 'OK']) : $response;
    }

	/**
	 * Build and return response.
	 * @param string|array $data Data.
	 * @param int $status HTTP code. 200 by default for OK.
	 * @param string $reponseType Response type (json, xml). "json" by default.
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function view(&$data, int $status = 200, string $viewType = 'json'): Response
	{
		return $this->$viewType($data, $status);
	}

	/**
	 * Check expected environment. If environment is not expected, then throw \App\Exception\ServiceUnavailableException.
	 * @param string $expectedEnvironment Expected environment to pass. "dev" by default.
	 * @return bool
	 * @throws \App\Exception\ServiceUnavailableException
	 */
	public function checkExpectedEnvironment(string $expectedEnvironment = 'dev'): void
	{
		// Case environment not expected
		if($_ENV['APP_ENV'] !== $expectedEnvironment) {
			throw new \App\Exception\ServiceUnavailableException('Execution in ' . $expectedEnvironment . ' environment expected: ' . $_SERVER['REDIRECT_URL']);
		}
	}
}
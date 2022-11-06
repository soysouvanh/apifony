<?php
namespace App\Bo;

use Symfony\Component\HttpFoundation\Response;

/**
 * Abstract aspect. All aspect classes must inherit from this abstract class.
 */
abstract class AbstractBo {
	/**
	 * Controller instance.
	 * @var \App\Controller\ApifonyController
	 */
	public \App\Controller\ApifonyController $controller;

	/**
	 * Data sources.
	 * @var array
	 */
	protected array $dataSources;

	/**
	 * Request form data: define all request parameters.
	 * @var null|array
	 */
	protected ?array $formData = null;

	/**
	 * Request parameters from controller.
	 * @var array
	 */
	protected array $parameters;

	/**
	 * (non-PHPdoc)
	 * @see App\Controller\ApiController::__construct
	 */
	/*public function __construct(\App\Controller\ApifonyController &$controller)
	{
		// Retrieve request parameters
		$this->controller = $controller;
		$this->formData = $controller->getFormData();
		$this->parameters = $controller->getParameters();
	}*/
	public function __construct(\App\Controller\ApifonyController &$controller, array &$dataSources, ?array &$formData, array &$parameters)
	{
		// Set attributes
		$this->controller = &$controller;
		$this->dataSources = &$dataSources;
		$this->formData = &$formData;
		$this->parameters = &$parameters;
	}

	/**
	 * Build and return JSON response.
	 * @param string|array $data Data.
	 * @param int $status HTTP code. 200 by default for OK.
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function json(&$data, int $status = 200): Response
	{
		// Return JSON view
		return $this->controller->view($data, $status, 'json');
	}

	/**
	 * Build and return XML response.
	 * @param string|array $data Data.
	 * @param int $status HTTP code. 200 by default for OK.
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function xml(&$data, int $status = 200): Response
	{
		// Return XML view
		return $this->controller->view($data, $status, 'xml');
	}

	/**
	 * Instanciate and retur  data source instance if exists.
	 * @param string $instanceName Data source instance name.
	 * @return ?object
	 * @see /config/data-sources.<$_SERVER['SERVER_NAME']>.php
	 */
	public function __get(string $instanceName): ?object
	{
		// Case data source exists
		if(isset($this->dataSources[$instanceName])) {
			// Case data source already instanciate in begin transaction
			if(is_object($this->dataSources[$instanceName])) {
				return $this->dataSources[$instanceName];
			}

			// Retrieve connection data
			$connectionData = $this->dataSources[$instanceName];
			$connectionData['dataSourceName'] = $instanceName;
			
			// Case user name and password crypted
			if($connectionData['crypted']) {
				// Decrypt userName and password
				$connectionData['userName'] = $this->controller::decrypt($connectionData['userName']);
				$connectionData['password'] = $this->controller::decrypt($connectionData['password']);
			}

			// Create data source instance
			$dsClass = '\\App\\Service\\' . ucfirst($this->dataSources[$instanceName]['type']) . 'Service';
			try {
				$this->$instanceName = new $dsClass($connectionData);
			} catch(\Throwable $e) {
				throw new \App\Exception\DataSourceException('Instanciate data source impossible: ' . $dsClass . ': ' . $instanceName . ' - ' . $e->getMessage());
			}

			// Add data source in the data sources list to manage commit and rollback
			if($this->dataSources[$instanceName]['type'] !== 'redis') {
				$this->controller->addDataSource($instanceName, $this->$instanceName);
			}

			// Return data source instance
			return $this->$instanceName;
		}

		// Case instance not found
		return null;
	}

	/**
	 * Throw form data exception.
	 * @param string $fieldId Field identifier.
	 * @param string $type (optional) Error type (format, required, minLength, maxLength, messages). "format" by default.
	 * @param string $messageKey (optional) Message key containing the message. "message" by default.
	 * @return void
	 * @throws \App\Exception\FormDataException
	 */
	public function throwFormDataException(string $fieldId, string $type = 'format', string $messageKey = 'message'): void {
		//Throw form data exception
		throw new \App\Exception\FormDataException(
			json_encode(
				[
					'fieldId' => &$fieldId,
					'type' => &$type,
					'message' => &$this->formData[$fieldId][$type][$messageKey],
					'label' => &$this->formData[$fieldId]['title']
				],
				\JSON_UNESCAPED_SLASHES
			)
		);
	}
}
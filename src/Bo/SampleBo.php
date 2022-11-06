<?php
namespace App\Bo;

use Symfony\Component\HttpFoundation\Response;

/**
 * Sample business object.
 */
class SampleBo extends \App\Bo\AbstractBo {
	/**
	 * Run ::getParameters business logic.
	 * @return void
	 */
	public function getParametersCheck(): void
	{
		//echo 'getParametersCheck';
	}

	/**
	 * Run main action and return response.
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function getParameters(): Response
	{
		// Retrieve parameters
		$parameters = $this->parameters;
		
		$this->redis->set('v', 'trace redis value');
		$parameters['redis'] = $this->redis->get('v');
		$this->redis->delete('v');

		$i = date('His');
		$data = [
			'simpleIndex' => 'simpleIndex' . $i,
			'index1on2' => $i,
			'index2on2' => $i + 1,
			'label' => 'label' . $i,
			'description' => 'description' . $i
		];
		$this->mysql->sampleDao->insert($data);
		
		// Return view data
		return $this->json($parameters);
	}
}
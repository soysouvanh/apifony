<?php 
namespace App\Controller;

//use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Aspect weaver controller using aspect trait.
 * If request not found in /config/routes.yaml, then redirect to ApifonyController.
 */
class ApifonyController extends AbstractController
{
	// Add aspect inspired behaviour
	use \App\Trait\AspectTrait {
		__construct as __traitConstruct;
		authenticate as traitAuthenticate;
		checkServicePermission as traitCheckServicePermission;
	}
	
	/**
	 * Uncomment to overwrite trait ::__construct.
	 * @return void
	 */
	/*public function __construct(RequestStack $requestStack, LoggerInterface $logger)
	{
		$this->__traitConstruct($requestStack, $logger);
	}*/

	/**
	 * Uncomment to overwrite trait ::authenticate.
	 * Authenticate client. Remove if not used.
	 * Customize by overwriting \App\Trait\AspectTrait::authenticate.
	 * @return void
	 * @throws \App\Exception\AuthenticationException
	 */
	/*public function authenticate(): void
	{
		// By default, call the trait authentication using /config/clients.php
		$this->traitAuthenticate();
	}*/

	/**
	 * Uncomment to overwrite trait ::checkServicePermission.
	 * Check client service permission. Remove if not used.
	 * Customize by overwriting \App\Trait\AspectTrait::checkServicePermission.
	 * @return void
	 * @throws \App\Exception\ForbiddenException
	 */
	/*public function checkServicePermission(): void
	{
		// By default, call the trait checkServicePermission using /config/<ip>.php
		$this->traitCheckServicePermission();
	}*/
}
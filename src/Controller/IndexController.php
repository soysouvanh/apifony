<?php 
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends AbstractController
{
	/**
	 * #[Route('/', name: 'api_index')]
	 * Page d'index
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @uses https://xxx/
	 */
    public function index(Request $request): Response
    {
		return $this->json($_REQUEST);
    }
	
	/**
	 * #[Route('/php-version', name: 'api_php_version')]
	 * Sample: return PHP version.
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @uses https://xxx/php-version
	 */
	public function phpVersion(): Response
	{
		return $this->json([
			'phpVersion' => phpversion()
		]);
	}

	/**
	 * #[Route('/php-info', name: 'api_php_info')]
	 * Sample: display PHP info.
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @uses https://xxx/php-info
	 */
	public function phpInfo(): Response
	{
		\phpinfo();
		die;
	}
}
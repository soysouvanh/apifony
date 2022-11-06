<?php  
namespace App\Exception;

/**
 * Service unavailable exception. Occurs when the server cannot handle the request (because it is overloaded or down for maintenance). Generally, this is a temporary state.
 */
class ServiceUnavailableException extends \App\Exception\AbstractException {
	/**
	 * The exception code.
	 * @var int
	 */
	protected $code = 503;

    /**
	 * The exception message.
	 * @var string
	 */
    protected $message = 'Service Unavailable';
}
?>
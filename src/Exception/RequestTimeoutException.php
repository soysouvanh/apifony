<?php  
namespace App\Exception;

/**
 * Method not available exception. Occurs when a method must be implemented but which is not available.
 */
class RequestTimeoutException extends \App\Exception\AbstractException {
	/**
	 * The exception code.
	 * @var int
	 */
	protected $code = 408;

    /**
	 * The exception message.
	 * @var string
	 */
    protected $message = 'Request Timeout';
}
?>
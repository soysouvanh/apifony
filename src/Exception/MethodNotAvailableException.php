<?php  
namespace App\Exception;

/**
 * Method not available exception. Occurs when a method must be implemented but which is not available.
 */
class MethodNotAvailableException extends \App\Exception\AbstractException {
	/**
	 * The exception code.
	 * @var int
	 */
	protected $code = 500;

    /**
	 * The exception message.
	 * @var string
	 */
    protected $message = 'Internal Server Error';
}
?>
<?php  
namespace App\Exception;

/**
 * Not implemented request method exception. Occurs when the server either does not recognize the request method, or it lacks the ability to fulfil the request.
 */
class NotImplementedException extends \App\Exception\AbstractException {
	/**
	 * The exception code.
	 * @var int
	 */
	protected $code = 501;

    /**
	 * The exception message.
	 * @var string
	 */
    protected $message = 'Not Implemented';
}
?>
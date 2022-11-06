<?php  
namespace App\Exception;

/**
 * Session exception. Occurs when user session is expired.
 */
class SessionException extends \App\Exception\AbstractException {
	/**
	 * The exception code.
	 * @var int
	 */
	protected $code = 403;

    /**
	 * The exception message.
	 * @var string
	 */
    protected $message = 'Forbidden';
}
?>
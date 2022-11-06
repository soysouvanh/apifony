<?php  
namespace App\Exception;

/**
 * Authentication exception. Occurs when login or password is invalid.
 */
class AuthenticationException extends \App\Exception\AbstractException {
	/**
	 * The exception code.
	 * @var int
	 */
	protected $code = 401;

    /**
	 * The exception message.
	 * @var string
	 */
    protected $message = 'Unauthorized';
}
?>
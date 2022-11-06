<?php  
namespace App\Exception;

/**
 * Crypt exception. Occurs when an encryption is impossible.
 */
class CryptException extends \App\Exception\AbstractException {
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
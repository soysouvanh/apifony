<?php  
namespace App\Exception;

/**
 * Bad response exception. Occurs when response is not expected.
 */
class BadResponseException extends \App\Exception\AbstractException {
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
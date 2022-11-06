<?php  
namespace App\Exception;

/**
 * Bad request exception. Occurs when SQL is invalid.
 */
class BadRequestException extends \App\Exception\AbstractException {
	/**
	 * The exception code.
	 * @var int
	 */
	protected $code = 400;

    /**
	 * The exception message.
	 * @var string
	 */
    protected $message = 'Bad Request';
}
?>
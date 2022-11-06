<?php  
namespace App\Exception;

/**
 * Duplication exception. Occurs when a resource already exists on creation action.
 */
class DuplicationException extends \App\Exception\AbstractException {
	/**
	 * The exception code.
	 * @var int
	 */
	protected $code = 409;

    /**
	 * The exception message.
	 * @var string
	 */
    protected $message = 'Conflict';
}
?>
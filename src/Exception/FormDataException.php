<?php 
namespace App\Exception;

/**
 * Form data exception. Occurs when a request data (from query or form data) is not expected.
 */
class FormDataException extends \App\Exception\AbstractException {
	/**
	 * The exception code.
	 * @var int
	 */
	protected $code = 412;

    /**
	 * The exception message.
	 * @var string
	 */
    protected $message = 'Precondition Failed';
}
?>
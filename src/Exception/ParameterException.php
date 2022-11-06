<?php  
namespace App\Exception;

/**
 * Parameter exception. Occurs when a parameter value is not expected.
 */
class ParameterException extends \App\Exception\AbstractException {
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
<?php  
namespace App\Exception;

/**
 * Framework exception. Occurs when the framework throws an exception different from App\Exception\xxxException.
 */
class FrameworkException extends \App\Exception\AbstractException {
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
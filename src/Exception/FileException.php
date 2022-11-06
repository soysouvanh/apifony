<?php  
namespace App\Exception;

/**
 * File exception. Occurs when there is a problem on reading or writting a file.
 */
class FileException extends \App\Exception\AbstractException {
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
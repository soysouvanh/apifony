<?php 
namespace App\Exception;

/**
 * File unfound exception. Occurs when a file is not found.
 */
class FileNotFoundException extends \App\Exception\AbstractException {
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
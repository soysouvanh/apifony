<?php 
namespace App\Exception;

/**
 * File unfound exception. Occurs when a resource is not found.
 */
class NotFoundException extends \App\Exception\AbstractException {
	/**
	 * The exception code.
	 * @var int
	 */
	protected $code = 404;

    /**
	 * The exception message.
	 * @var string
	 */
    protected $message = 'Not Found';
}
?>
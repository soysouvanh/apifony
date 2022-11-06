<?php  
namespace App\Exception;

/**
 * Forbidden exception. Occurs when a permission is denied on a resource.
 */
class ForbiddenException extends \App\Exception\AbstractException {
	/**
	 * The exception code.
	 * @var int
	 */
	protected $code = 403;

    /**
	 * The exception message.
	 * @var string
	 */
    protected $message = 'Forbidden';
}
?>
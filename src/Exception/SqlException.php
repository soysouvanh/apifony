<?php  
namespace App\Exception;

/**
 * SQL default exception. Occurs when an SQL error appears.
 */
class SqlException extends \App\Exception\AbstractException {
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
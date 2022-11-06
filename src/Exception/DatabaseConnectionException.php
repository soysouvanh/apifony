<?php 
namespace App\Exception;

/**
 * Database connection exception. Occurs when connection with database is impossible.
 */
class DatabaseConnectionException extends \App\Exception\AbstractException {
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
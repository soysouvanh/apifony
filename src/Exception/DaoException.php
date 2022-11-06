<?php  
namespace App\Exception;

/**
 * DAO exception. Occurs when a DAO is not found or can not be instanciated.
 */
class DaoException extends \App\Exception\AbstractException {
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
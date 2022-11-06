<?php  
namespace App\Exception;

/**
 * Data source exception. Occurs when a data source is not found or can not be instanciated.
 */
class DataSourceException extends \App\Exception\AbstractException {
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
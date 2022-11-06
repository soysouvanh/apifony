<?php  
namespace App\Exception;

/**
 * Configuration exception. Occurs when configuration is impossible.
 */
class ConfigurationException extends \App\Exception\AbstractException {
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
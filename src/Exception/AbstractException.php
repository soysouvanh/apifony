<?php  
namespace App\Exception;

/**
 * Abstract exception.
 */
abstract class AbstractException extends \Exception {
    /**
	 * Message to log.
	 * @var string
	 */
    public readonly string $logMessage;

	/**
	 * Construct the exception.
	 * @param string $logMessage (optional) The message to log. null by default.
	 * @param string $message (optional) The Exception message to throw. "" by default.
	 * @param int $code (optional) The Exception code. 0 by default.
	 * @param ?\Throwable $previous (optional) The previous exception used for the exception chaining. null by default.
	 * @return self
	 */
	public function __construct(string $logMessage = '', string $message = '', int $code = 0, ?\Throwable $previous = null)
	{
		// Construct the exception
		parent::__construct($message !== '' ? $message : $this->message, $code !== 0 ? $code : $this->code, $previous);

		// Set log message
		$this->logMessage = $logMessage;
	}
}
?>
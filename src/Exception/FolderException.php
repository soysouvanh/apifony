<?php  
namespace App\Exception;

/**
 * Folder exception. Occurs when there is a problem on reading or writting a folder.
 */
class FolderException extends \App\Exception\AbstractException {
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
<?php 
namespace App\Service;

/**
 * Log manager.
 */
class LogManager {
	/**
	 * Information log type.
	 * @var string
	 */
	const INFORMATION_LOG = 'information';
	
	/**
	 * Warning log type.
	 * @var string
	 */
	const WARNING_LOG = 'warning';
	
	/**
	 * Error log type.
	 * @var string
	 */
	const ERROR_LOG = 'error';
	
	/**
	 * Fatal log type.
	 * @var string
	 */
	const FATAL_LOG = 'fatal';
	
	/**
	 * Return client IP (ex: 83.206.16.131).
	 * @return string
	 */
	static public function getIp(): string {
		// Retrieve IP
		$ip = null;
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		elseif(isset($_SERVER['HTTP_X_REAL_IP'])) {
			$ip = $_SERVER['HTTP_X_REAL_IP'];
		}
		elseif(isset($_SERVER['HTTP_X_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_X_CLIENT_IP'];
		}
		elseif(isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif(isset($_SERVER['HTTP_X_FORWARDED'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED'];
		}
		elseif(isset($_SERVER['HTTP_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_FORWARDED_FOR'];
		}
		elseif(isset($_SERVER['HTTP_FORWARDED'])) {
			$ip = $_SERVER['HTTP_FORWARDED'];
		}
		elseif(isset($_SERVER['HTTP_VIA'])) {
			$ip = $_SERVER['HTTP_VIA'];
		}
		elseif(isset($_SERVER['HTTP_X_COMING_FROM'])) {
			$ip = $_SERVER['HTTP_X_COMING_FROM'];
		}
		elseif(isset($_SERVER['HTTP_COMING_FROM'])) {
			$ip = $_SERVER['HTTP_COMING_FROM'];
		}
		
		// Case IP contacted format
		if($ip == ':::1') {
			// Convert to complete IP format
			$ip = '127.0.0.1';
		}
		
		// Retrieve IP behind proxy, otherwise by default without proxy ($_SERVER['REMOTE_ADDR'])
		$matches = null;
		return $ip !== null ? (preg_match('/^(\d{1,3}\.){3}\d{1,3}$/', $ip, $matches) ? $matches[0] : $_SERVER['REMOTE_ADDR']) : $_SERVER['REMOTE_ADDR'];
	}
	
	/**
	 * Log message. $_logData is expected, otherwise apache error by default (see /var/log/apache2/error.log).
	 * @param \Throwable $exception Exception occured.
	 * @param \App\Service\AbstractPdo $pdo (optionnel) pdo.
	 * @param string $type (optionnel) Log type: information, warning, error, fatal. "error" by default.
	 * @return void
	 */
	static public function log(\Throwable $exception, ?\App\Service\AbstractPdo $pdo = null, string $type = self::ERROR_LOG): void {
		//Load log configuration: $_logData
		$_logData = null;
		$fileName = $_SERVER['DOCUMENT_ROOT'] . '/../config/log.php';
		if(is_file($fileName)) {
			require $fileName;
		}

		//Case log disabled
		if($_logData === null) {
			return;
		}
		
		//Retrieve error trace
		$traces = $exception->getTrace();

		if(!isset($traces[0]['line'])) {
			$traces[0]['line'] = $exception->getLine();
		}

		//Case log in database
		if($pdo !== null && $_logData['application'][$type . 'DbTable'] !== null) {
			try {
				$tableName = 'adm_' . $type;
				//$IdColumnName = $type . '_id';
				
				$data = [
					//$IdColumnName => $middleware->getNewId($tableName, $IdColumnName),
					'message' => substr($exception->getMessage(), 0, 512),
					'uri' => &$_SERVER['REQUEST_URI'],
					'parameters' => !empty($_REQUEST) ? json_encode($_REQUEST, JSON_UNESCAPED_SLASHES) : '',
					'method' => &$_SERVER['REQUEST_METHOD'],
					'trace' => substr(json_encode($traces[0], JSON_UNESCAPED_SLASHES), 0, 2048),
					'user_name' => isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : (isset($_SESSION['admin']['user_name']) ? $_SESSION['admin']['user_name'] : substr($_SERVER['HTTP_USER_AGENT'], 0, 128)),
					'ip' => self::getIp(),
					'created_date' => date('Y-m-d'),
					'created_time' => date('H:i:s')
				];
				
				//Case user_name with "Client" sufix  
				$data['user_name'] = str_replace('Client', '', $data['user_name']);

				//Insert log in database
				$pdo->insert($tableName, $data);

				//Count number of logs added of the day
				//Case first log of the day
				if(1 === $pdo->count($tableName, ['created_date' => &$data['created_date']])) {
					//Notify error or warning to administrator(s)
					\App\Service\NotificationManager::sendEmail($type, $data, $pdo);
				}
				return;
			} catch(\Throwable $e) {
				//Case log in file
			}
		}
		
		//Case log in log file
		//Case application log by default
		if(isset($_logData['application']['MAIL_ERROR_LOG']) || isset($_logData['application']['FILE_ERROR_LOG'])) {
			//Build error message
			$errorMessage = "\n" . date('Y-m-d H:i:s') . ' ' . self::getIp() . ' ' . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI']
				. "\n" . $exception->getMessage()
				//. "\n" . print_r($traces[0], true)
				//. (isset($traces[1]['function']) && $traces[1]['function'] != '{closure}' ? print_r($traces[1], true) : '')
				;
			
			//Build error log file
			$errorFile = $_logData['application']['FILE_ERROR_LOG'] . $type . date('-Ymd') . '.log';
			
			//Case log error in file
			if($_logData['application']['FILE_ERROR_LOG'] !== null) {
				//Append error in application error file
				error_log($errorMessage, 3, $errorFile);
			}
			
			//Case send notification by email only if the day log does not exist yet to avoid too much emails
			if($_logData['application']['MAIL_ERROR_LOG'] !== null && !is_file($errorFile)) {
				//Send error message by email
				//$headers = 'Subject: Error ' . $_SERVER['HTTP_HOST'] . PHP_EOL
				//	. 'From: ' . $_logData['application']['MAIL_ERROR_LOG'] . PHP_EOL;
				//	//. 'MIME-Version: 1.0' . PHP_EOL
				//	//. 'Content-Type: text/html; charset=ISO-8859-1' . PHP_EOL;
				//error_log($errorMessage, 1, $_logData['application']['MAIL_ERROR_LOG'], $headers);
				error_log($errorMessage, 1, $_logData['application']['MAIL_ERROR_LOG'], $_logData['application']['MAIL_ERROR_LOG']);
			}
		}
		
		//No catch exception by default: apache log used
		//throw $exception;
	}
}
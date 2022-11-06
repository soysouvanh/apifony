<?php 
namespace App\Service;

/**
 * Gestionnaire de mots de passe.
 * Exemples d'appel depuis un controller :
 *		- \App\Service\Password::generate(12, 20, true)
 *		- \App\Service\Password::isValid($password, 12, 20, true, $error)
 *		- \App\Service\Password::hash($password)
 */
class Password
{
	/**
	 * Liste des chiffres;
	 * @var string
	 */
	const DIGITS = '0123456789';
	
	/**
	 * Liste des minuscules.
	 * @var string
	 */
	const LOWER_CASES = 'abcdefghijklmnopqrstuvwxyz';
	
	/**
	 * Liste des majuscules.
	 * @var string
	 */
	const UPPER_CASES = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	
	/**
	 * Liste des caractères spéciaux.
	 * @var string
	 */
	const SPECIAL_CHARACTERS = '!"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~';
	
	/**
	 * Liste des erreurs.
	 * @var array
	 */
	const ERRORS = [
		0 => 'OK',
		1 => 'Password too short (<minLength> characters at least are expected).',
		2 => 'Password too long (<maxLength> characters at most are expected).',
		3 => 'Numeric character is expected.',
		4 => 'Lowercase letter is expected.',
		5 => 'Uppercase letter is expected.',
		6 => 'Special character is expected.'
	];
	
	/**
	 * Liste de multiplicateurs.
	 * @var array
	 */
	const MULTIPLIERS = [3, 6, 9];
	
	/**
	 * Génère et retourne un mot de passe généré de façon aléatoire.
	 * @param int $minLength Longueur minimale du mot de passe généré. Cette longueur ne peut être inférieure à 8 caractères. 12 par défaut.
	 * @param int $maxLength Longueur maximale du mot de passe généré. 20 par défaut.
	 * @param bool $strategyExpected true pour être conforme à la stratégie de sécurité : le mot de passe doit contenir au moins un chiffre, une minuscule, une majuscule et un caractère spécial. true par défaut.
	 * @return string
	 * @throws \App\Exception\ParameterException
	 */
	static public function generate(int $minLength = 12, int $maxLength = 20, bool $strategyExpected = true): string
	{
		// Vérife la longueur minimale
		if($minLength < 8) {
			// La longueur minimale ne peut être inférieure à 8 caractères
			throw new \App\Exception\ParameterException('Minimum length expected at least 8 characters.');
		}
		
		// Vérifie la longueur maximale
		if($minLength > $maxLength) {
			// Permute les valeurs entre la longueur minimale et la longueur maximale
			$t = $minLength;
			$minLength = $maxLength;
			$maxLength = $t;
		}
		
		// Définit la longueur du mot de passe à générer, sans les 4 derniers caractères correspondant aux 4 types de caractères :
		//	- chiffres
		//	- minuscules
		//	- majuscules
		//	- caractères spéciaux
		$length = random_int($minLength, $maxLength) - ($strategyExpected ? 4 : 0);
		
		// Mélange tous les caractères autorisés pour la génération
		$characters = str_shuffle(self::DIGITS . self::LOWER_CASES . self::UPPER_CASES . self::SPECIAL_CHARACTERS);
		$maxIndex = 10 + 26 + 26 + 32 - 1; //strlen($characters) - 1;
		
		// Génère le mot de passe
		$password = '';
		for($i = 0; $i < $length; $i++) {
			$password .= $characters[random_int(0, $maxIndex)];
		}
		
		// Applique la stratégie de sécurité du mot de passe
		// Le mot de passe doit contenir au moins un chiffre, une minuscule, une majuscule et un caractère spécial
		if($strategyExpected) {
			// Ajoute un chiffre aléatoire au mot de passe généré
			$password .= self::DIGITS[random_int(0, 9)];
			
			// Ajoute une minuscule aléatoire au mot de passe généré
			$password .= self::LOWER_CASES[random_int(0, 25)];
			
			// Ajoute une majuscule aléatoire au mot de passe généré
			$password .= self::UPPER_CASES[random_int(0, 25)];
			
			// Ajoute un caractère spacial aléatoire au mot de passe généré
			$password .= self::SPECIAL_CHARACTERS[random_int(0, 31)];
			
			// Mélange les caractères du mot de passe généré
			$password = str_shuffle($password);
		}
		
		// Retourne le mot de passe généré
		return $password;
	}
	
	/**
	 * Vérifie qu'il existe au moins un caractère de type $characterType dans le mot de passe.
	 * @param string $password Mot de passe.
	 * @param int $characterType Type de caractère : 1=chiffre, 2=minuscule, 3=majuscule, 4=caractère spécial.
	 * @return bool
	 * @throws \App\Exception\ParameterException
	 */
	static protected function _hasAtLeast(string $password, int $characterType): bool
	{
		// Contrôle le type de caractère
		if($characterType < 1 || $characterType > 4) {
			throw new \App\Exception\ParameterException('$characterType Parameter value unknown: 1, 2, 3 or 4 is expected.');
		}
		
		// Détermine la liste des caractères associée au type de caractère à contrôler
		$characters = [
			1 => self::DIGITS,
			2 => self::LOWER_CASES,
			3 => self::UPPER_CASES,
			4 => self::SPECIAL_CHARACTERS
		][$characterType];
		
		// Vérifie la présence d'au moins un caractère de $characters dans $password
		$passwordLength = strlen($password);
		$charactersLength = strlen($characters);
		$found = false;
		for($i = 0; $i < $passwordLength; $i++) {
			for($j = 0; $j < $charactersLength; $j++) {
				if($password[$i] == $characters[$j]) {
					$found = true;
					break;
				}
			}
			if($found) {
				break;
			}
		}
		
		// Retourne le récultat de la recherche
		return $found;
	}
	
	/**
	 * Détermine la validité du mot de passe en fonction des paramètres $minLength, $maxLength et $strategyExpected.
	 * @param int $minLength Longueur minimale du mot de passe généré. Cette longueur ne peut être inférieure à 8 caractères. 12 par défaut.
	 * @param int $maxLength Longueur maximale du mot de passe généré. 128 par défaut.
	 * @param bool $strategyExpected true pour être conforme à la stratégie de sécurité : le mot de passe doit contenir au moins un chiffre, une minuscule, une majuscule et un caractère spécial. true par défaut.
	 * @param array $error Tableau associatif passé en référence pour récupérer le message d'erreur. ['code' => 0, 'message' => 'OK'] par défaut pour aucune erreur.
	 * @return bool
	 */
	static public function isValid(string $password, int $minLength = 12, int $maxLength = 128, bool $strategyExpected = true, array &$error = null): bool
	{
		// Détermine la longueur du mot de passe
		$length = strlen($password);
		
		// Vérifie la longueur minimale
		if($length < $minLength) {
			$error = ['code' => 1, 'message' => self::ERRORS[1]];
			return false;
		}
		
		// Vérifie la longueur maximale
		if($length > $maxLength) {
			$error = ['code' => 2, 'message' => self::ERRORS[2]];
			return false;
		}
		
		// Vérifie l'application de la stratégie de sécurité
		if($strategyExpected) {
			// Vérifie la présence d'au moins un chiffre
			if(!self::_hasAtLeast($password, 1)) {
				$error = ['code' => 3, 'message' => self::ERRORS[3]];
				return false;
			}
			
			// Vérifie la présence d'au moins une minuscule
			if(!self::_hasAtLeast($password, 2)) {
				$error = ['code' => 4, 'message' => self::ERRORS[4]];
				return false;
			}
			
			// Vérifie la présence d'au moins une majuscule
			if(!self::_hasAtLeast($password, 3)) {
				$error = ['code' => 5, 'message' => self::ERRORS[5]];
				return false;
			}
			
			// Vérifie la présence d'au moins un caractère spécial
			if(!self::_hasAtLeast($password,4)) {
				$error = ['code' => 6, 'message' => self::ERRORS[6]];
				return false;
			}
		}
		
		// Le mot de passe est valide
		$error = ['code' => 0, 'message' => 'OK'];
		return true;
	}
	
	/**
	 * Retourne la clé numérique issue d'une chaîne de caractère.
	 * @param string $string Chaîne de caractères.
	 * @return int
	 */
	static public function getSeed(string $string): int
	{
		// Initialize variables
		$seed = 0;
		$length = strlen($string);
		
		// Compute the seed
		for($i = 0; $i < $length; $i++) {
			$seed += ord($string[$i]) * self::MULTIPLIERS[$i % 3];
		}
		
		// Return the seed
		return (int)implode('', self::MULTIPLIERS) * $seed;
	}
	
	/**
	 * Convertit une clé numérique en une suite héxadécimale.
	 * @param int $seed Clé numérique à convertir.
	 * @return string
	 */
	static public function seedToHex(int $seed): string
	{
		// Découpe la clé en éléments de 2 caractères
		$elements = str_split((string)$seed, 2);
		
		// Boucle sur les éléments
		foreach($elements as &$element) {
			// Convertit l'élément en héxadécimal
			$element = dechex((int)$element);
		}
		
		// Retourne le résultat de la conversion
		return implode('', $elements);
	}
	
	/**
	 * Convertit une suite héxadécimale en clé numérique.
	 * @param string $hex Suite héxadécimale à convertir.
	 * @return int
	 */
	static public function hexToSeed(string $hex): int
	{
		// Découpe la clé en éléments de 2 caractères
		$elements = str_split($hex, 2);
		
		// Boucle sur les éléments
		foreach($elements as &$element) {
			// Convertit l'élément en héxadécimal
			$element = hexdec($element);
		}
		
		// Retourne le résultat de la conversion
		return (int)implode('', $elements);
	}
	
	/**
	 * Mélange les caractères de la chaîne de caractères.
	 * @param string $string Chaîne de caractères à mélanger.
	 * @param int $seed (optionnel) Clé numérique. 0 par défaut pour récupérer la clé déduite de la chaîne de caractère.
	 * @return string
	 */
	public static function shuffle(string $string, int $seed = 0): string
	{
		// Cas clé non renseignée
		if($seed === 0) {
			// Récupère la clé propre à la chaîne de caractères
			$seed = self::getSeed($string);
		}
		
		// Mélange les caractères
		$stringLength = strlen($string);
		$seedLength = strlen($seed = (string)$seed);
		for($i = 0; $i < $stringLength; $i++) {
			$swap = $seed[$i % $seedLength] % $stringLength;
			$temp = $string[$swap];
			$string[$swap] = $string[$i];
			$string[$i] = $temp;
		}
		
		// Retourne le résultat du mélange
		return $string;
	}
	
	/**
	 * Démêle la chaîne de caractères.
	 * @param string $string Chaîne de caractères à démêler.
	 * @param int $seed (optionnel) Clé numérique. 0 par défaut pour récupérer la clé déduite de la chaîne de caractère.
	 * @return string
	 */
	public static function unshuffle(string $string, int $seed = 0): string
	{
		// Cas clé non renseignée
		if($seed === 0) {
			// Récupère la clé propre à la chaîne de caractères
			$seed = self::getSeed($string);
		}
		
		// Démême les caractères
		$stringLength = strlen($string);
		$seedLength = strlen($seed = (string)$seed);
		$iMax = $stringLength - 1;
		for($i = $iMax; $i >= 0; $i--) {
			$swap = $seed[$i % $seedLength] % $stringLength;
			$temp = $string[$swap];
			$string[$swap] = $string[$i];
			$string[$i] = $temp;
		}
		
		// Retourne le résultat du démêlage
		return $string;
	}

	/**
	 * Crypte une chaîne de caractères.
	 * @param string $string Chaîne de caractères à crypter.
	 * @return string
	 */
	static public function encrypt(string $string, string $method = 'aes-256-cbc'): string
	{
		/*
		// Récupère la clé
		$seed = self::getSeed($string);
		
		// Mélange les caractères
		$string = self::shuffle($string, $seed);
		
		// Crypte la chaîne de caractère
		$first_key = base64_decode($_SERVER['SERVER_NAME']);
		$second_key = base64_decode(\App\Service\LogManager::getIp());
		
		$method = 'aes-256-cbc';
		$iv_length = openssl_cipher_iv_length($method);
		$iv = openssl_random_pseudo_bytes($iv_length);
		
		$first_encrypted = openssl_encrypt($string, $method, $first_key, OPENSSL_RAW_DATA, $iv);
		$second_encrypted = hash_hmac('sha3-512', $first_encrypted, $second_key, TRUE);
		
		// Convertit en base 64
		$string = base64_encode($iv . $second_encrypted . $first_encrypted);
		
		// Insère un point au hasard
		$length = strlen($string);
		$i = random_int(1, $length);
		$string = substr($string, 0, $i) . '.' . substr($string, $i);
		
		// Concatène la clé convertie en héxadécimal à la suite de la chaîne de caractères
		return $string .= '.' . base64_encode(self::seedToHex($seed));
		*/
		
		// Crypte la chaîne de caractère
		$first_key = base64_decode($_SERVER['SERVER_NAME']);
		$iv = '0987654321123456';
		return base64_encode(openssl_encrypt($string, $method, $first_key, OPENSSL_RAW_DATA, $iv));
	}
	
	/**
	 * Décrypte une chaîne de caractères cryptée.
	 * @param string $string Chaîne de caractères à décrypter.
	 * @return string
	 */
	static public function decrypt(string $string, string $method = 'aes-256-cbc'): string
	{
		/*
		// Récupère la clé et la chaîne de caractères
		$string = explode('.', $string);
		$seed = self::hexToSeed(base64_decode($string[2]));
		$string = $string[0] . $string[1];
		
		// Décrypte la chaîne de caractères
		$first_key = base64_decode($_SERVER['SERVER_NAME']);
		//$second_key = base64_decode(\App\Service\LogManager::getIp());
		$mix = base64_decode($string);
		
		$method = 'aes-256-cbc';
		$iv_length = openssl_cipher_iv_length($method);
		
		$iv = substr($mix, 0, $iv_length);
		//$second_encrypted = substr($mix, $iv_length, 64);
		$first_encrypted = substr($mix, $iv_length + 64);
		
		$string = openssl_decrypt($first_encrypted, $method, $first_key, OPENSSL_RAW_DATA, $iv);
		
		// Démêle la chaîne de caractères
		return self::unshuffle($string, $seed);
		*/
		
		// Décrypte la chaîne de caractères
		$first_key = base64_decode($_SERVER['SERVER_NAME']);
		$iv = '0987654321123456';
		return openssl_decrypt(base64_decode($string), $method, $first_key, OPENSSL_RAW_DATA, $iv);
	}
}
?>
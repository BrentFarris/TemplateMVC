<?php

/**
 * Generate a private key to be stored in the database
 * @param Int $count The length of the string to produce
 * @return string The generated private key that meets the current standard
 */
function generate_key($count = 32) {
	$availableLetters = 'qwertyuiopasdfghjklzxcvbnm1234567890ZXCVBNMASDFGHJKLQWERTYUIOP!@#$%^&*-=|?';

	$privateKey = '';
	$size = strlen($availableLetters) - 1;
	for ($i = 0; $i < $count; $i ++) {
		$privateKey .= $availableLetters[mt_rand(0, $size - 1)];
	}

	return $privateKey;
}

function generate_random_string($count = 32, $onlyCapitals = false) {
	$availableLetters = (!$onlyCapitals ? 'qwertyuiopasdfghjklzxcvbnm' : '') . '1234567890ZXCVBNMASDFGHJKLQWERTYUIOP';

	$privateKey = '';
	$size = strlen($availableLetters);
	for ($i = 0; $i < $count; $i++) {
		$privateKey .= $availableLetters[mt_rand(0, $size - 1)];
	}

	return $privateKey;
}

function generate_random_string_extended($count = 9) {
	$availableLetters = 'qwertyuiopasdfghjklzxcvbnm1234567890QWERTYUIOPASDFGHJKLZXCVBNM-_';

	$str = '';
	$size = strlen($availableLetters) - 1;
	for ($i = 0; $i < $count; $i++) {
		$str .= $availableLetters[mt_rand(0, $size - 1)];
	}

	return $str;
}

function crypto($str) {
	//$username = 'Admin';
	//$password = 'gf45_gdf#4hg';

	// A higher "cost" is more secure but consumes more processing power
	$cost = 10;

	// Create a random salt
	$salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');

	// Prefix information about the hash so PHP knows how to verify it later.
	// "$2a$" Means we're using the Blowfish algorithm. The following two digits are the cost parameter.
	$salt = sprintf("$2a$%02d$", $cost) . $salt;

	// Value:
	// $2a$10$eImiTXuWVxfM37uY4JANjQ==

	// Hash the password with the salt
	$hash = crypt($str, $salt);

	// Value:
	// $2a$10$eImiTXuWVxfM37uY4JANjOL.oTxqp7WylW7FCzx2Lc7VLmdJIddZq

	return $hash;
}

function generate_secure_token($length = 64) {
	return bin2hex(openssl_random_pseudo_bytes($length));
}

class Cryptography {

	const CRYPT_CYPHER = MCRYPT_RIJNDAEL_256;
	const CRYPT_MODE = MCRYPT_MODE_CBC;

	private $iv;
	private $ivSize;
	private $secretKey;

	/**
	 * Cryptography constructor.
	 * @param string $secretKey
	 */
	public function __construct($secretKey) {
		// Create the initialization vector for added security.
		$this->ivSize = mcrypt_get_iv_size(self::CRYPT_CYPHER, MCRYPT_MODE_ECB);
		$this->iv = mcrypt_create_iv($this->ivSize, MCRYPT_RAND);

		$this->secretKey = $secretKey;
	}

	/**
	 * @param string $string
	 * @return string
	 */
	public function Encrypt($string) {
		return base64_encode($this->iv . mcrypt_encrypt(self::CRYPT_CYPHER, $this->secretKey, $string, self::CRYPT_MODE, $this->iv));
	}

	/**
	 * @param string $string
	 * @return string
	 */
	public function Decrypt($string) {
		$decodedString = base64_decode($string);
		$decodedIv = substr($decodedString, 0, $this->ivSize);
		$cypherText = substr($decodedString, $this->ivSize);
		return mcrypt_decrypt(self::CRYPT_CYPHER, $this->secretKey, $cypherText, self::CRYPT_MODE, $decodedIv);
	}
}
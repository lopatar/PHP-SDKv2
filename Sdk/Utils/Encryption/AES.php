<?php

namespace Sdk\Utils\Encryption;

use Sdk\Utils\Random;

/**
 * @see https://github.com/mervick/aes-everywhere/blob/master/php/src/AES256.php
 */
abstract class AES
{
	public static function encryptString(string $text, string $keyPhrase): string
	{
		$salt = Random::bytesSafe(8);
		$key = $iv = '';

		self::deriveKeyAndIV($salt, $keyPhrase, $key, $iv);
		return base64_encode('Salted__' . $salt . openssl_encrypt($text, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv));
	}

	private static function deriveKeyAndIV(string $salt, string $keyPhrase, string &$key, string &$iv): void
	{
		$salted = $dx = '';
		while (strlen($salted) < 48) {
			$dx = md5($dx . $keyPhrase . $salt, true);
			$salted .= $dx;
		}

		$key = substr($salted, 0, 32);
		$iv = substr($salted, 32, 16);
	}

	public static function decryptString(string $text, string $keyPhrase): string|null
	{
		$text = base64_decode($text);

		if ($text !== false && !str_starts_with($text, 'Salted__')) {
			return null;
		}

		$salt = substr($text, 8, 8);
		$text = substr($text, 16);

		$key = $iv = '';
		self::deriveKeyAndIV($salt, $keyPhrase, $key, $iv);

		return openssl_decrypt($text, 'aes-256-cbc', $key, true, $iv);
	}
}
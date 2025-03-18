<?php

namespace Sdk\Utils\Encryption;

use Sdk\IConfig;
use Sdk\Utils\Encryption\Exceptions\CryptoOperationFailed;
use Sdk\Utils\Random;

/**
 * @see https://github.com/mervick/aes-everywhere/blob/master/php/src/AES256.php
 */
//TODO: derive key using PBKDF2 or something
abstract class AES
{
    private static IConfig $config;

    public static function setConfig(IConfig $config): void
    {
        self::$config = $config;
    }

    public static function generateIV(): string
    {
        return Random::bytesSafe(self::getIVLength());
    }

    public static function getIVLength(): int
    {
        return openssl_cipher_iv_length(self::$config->getDefaultAesCipher());
    }

    public static function generateKey(): string
    {
        return Random::bytesSafe(self::getKeyLength());
    }

    public static function getKeyLength(): int
    {
        return openssl_cipher_key_length(self::$config->getDefaultAesCipher());
    }

    /**
     * @throws CryptoOperationFailed
     */
    public static function encryptString(string $plainText, string $encKey, string $iv): string|false
    {
        $encryptedData = openssl_encrypt($plainText, self::$config->getDefaultAesCipher(), $encKey, $iv);

        if ($encryptedData === false) {
            throw new CryptoOperationFailed("OpenSSL encryption failed");
        }

        return $encryptedData;
    }

    /**
     * @throws CryptoOperationFailed
     */
    public static function decryptString(string $cipherText, string $encKey, string $iv): string|false
    {
        $decryptedData = openssl_decrypt($cipherText, self::$config->getDefaultAesCipher(), $encKey, $iv);

        if ($decryptedData === false) {
            throw new CryptoOperationFailed("OpenSSL decryption failed");
        }

        return $decryptedData;
    }
}
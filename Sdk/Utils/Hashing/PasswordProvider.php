<?php

namespace Sdk\Utils\Hashing;

use Sdk\Utils\Hashing\Exceptions\InvalidPasswordAlgorithm;
use Sdk\Utils\Random;

final class PasswordProvider
{
    private static PasswordProvider $passwords;

    /**
     * @param string $passwordAlgorithm Password algorithm as in {@see password_algos()}
     * @param array $hashParameters Parameters passed to {@see password_hash()}
     * @throws InvalidPasswordAlgorithm When $passwordAlgorithm is not in {@see password_algos()}
     */
    public function __construct(public readonly string $passwordAlgorithm, public readonly array $hashParameters = [])
    {
        if (!in_array($passwordAlgorithm, password_algos())) {
            throw new InvalidPasswordAlgorithm($passwordAlgorithm);
        }
    }

    /**
     * Initializes the default provider accessible via {@see PasswordProvider::getDefaultProvider()}
     * @param string $passwordAlgorithm Password algorithm as in {@see password_algos()}
     * @param array $hashParameters Parameters passed to {@see password_hash()}
     * @throws InvalidPasswordAlgorithm When $passwordAlgorithm is not in {@see password_algos()}
     */
    public static function initDefaultProvider(string $passwordAlgorithm, array $hashParameters = []): void
    {
        self::$passwords = new self($passwordAlgorithm, $hashParameters);
    }

    /**
     * Returns the default password hashing provider
     * @return static|null Null when not initialized
     */
    public static function getDefaultProvider(): ?self
    {
        return self::$passwords ?? null;
    }

    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, $this->passwordAlgorithm, $this->hashParameters);
    }

    /**
     * @return array [
     *      'password' => 'plainTextPassword',
     *      'passwordHash' => 'passwordHash'
     * ]
     */
    public function generatePassword(): array
    {
        $password = Random::stringSafe(24);
        return ['password' => $password, 'passwordHash' => $this->hash($password)];
    }

    public function hash(string $password): string
    {
        return password_hash($password, $this->passwordAlgorithm, $this->hashParameters);
    }
}
<?php

namespace Sdk\Utils\Hashing;

use Sdk\Utils\Hashing\Exceptions\InvalidPasswordAlgorithm;
use Sdk\Utils\Random;

final class Passwords
{
    /**
     * @param string $passwordAlgorithm Password algorithm as in {@see password_algos()}
     * @param array $parameters Parameters passed to {@see password_hash()}
     * @throws InvalidPasswordAlgorithm When $passwordAlgorithm is not in {@see password_algos()}
     */
    public function __construct(public readonly string $passwordAlgorithm, public readonly array $parameters = [])
    {
        if (!in_array($passwordAlgorithm, password_algos())) {
            throw new InvalidPasswordAlgorithm($passwordAlgorithm);
        }
    }

    public function hash(string $password): string
    {
        return password_hash($password, $this->passwordAlgorithm, $this->parameters);
    }

    public function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, $this->passwordAlgorithm, $this->parameters);
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
}
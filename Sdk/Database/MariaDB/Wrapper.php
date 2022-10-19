<?php
declare(strict_types=1);

namespace Sdk\Database\MariaDB;

use mysqli;
use mysqli_result;

/**
 * @internal
 * @see https://github.com/lopatar/mysqli_wrapper
 */
final class Wrapper
{
	public readonly mysqli $connection;

	public function __construct(string $host, string $username, string $password, string $dbName)
	{
		$this->connection = new mysqli($host, $username, $password, $dbName);
	}

	public function __destruct()
	{
		$this->connection->close();
	}

	public function query(string $query, array $arguments = [], ?string $types = null): mysqli_result|false
	{
		if ($types === null && $arguments !== []) {
			$types = str_repeat('s', count($arguments));
		}

		$stmt = $this->connection->prepare($query);

		if ($stmt === false) {
			return false;
		}

		if (str_contains($query, '?')) {
			$stmt->bind_param($types, ...$arguments);
		}

		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();

		return $result;
	}
}
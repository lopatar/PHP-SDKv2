<?php
declare(strict_types=1);

namespace Sdk\Database\MariaDB;

use mysqli;
use mysqli_result;
use Sdk\Database\Exceptions\DatabaseObjectNotInitialized;

/**
 * Class that gives us ability to query MariDB database using prepared statements
 * @uses Wrapper
 */
abstract class Connection
{
    private static ?Wrapper $wrapper = null;

    /**
     * Initializes the internal {@see Wrapper} class, gets called by {@see App::initDatabaseConnection()}
     * @internal
     */
    public static function init(string $host, string $username, string $password, string $dbName): void
    {
        self::$wrapper = new Wrapper($host, $username, $password, $dbName);
    }

    /**
     * @param string $query The SQL query to execute (replace parameters with ?)
     * @param array $arguments
     * @param string|null $types A string of types (default s - string, for every argument passed)
     * @return mysqli_result|false False on failure
     * @throws DatabaseObjectNotInitialized
     */
    public static function query(string $query, array $arguments = [], ?string $types = null): mysqli_result|false
    {
        if (self::$wrapper === null) {
            throw new DatabaseObjectNotInitialized('MariaDB');
        }

        return self::$wrapper->query($query, $arguments, $types);
    }

    /**
     * This method gets the internal {@see mysqli} object from the {@see Wrapper} class
     * @return mysqli|null Null if not initialized
     */
    public static function getMysqlConnection(): ?mysqli
    {
        return self::$wrapper?->connection;
    }
}
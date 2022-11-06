<?php 
/**
 * Data sources configuration: data-sources.<HTTP_HOST>.php
 * For each data source (database), add the following row data:
 * '<data_source_name>' => [
 *     'type' => '<redis|pdo|mysql|mongodb|...>',
 *     'drive' => '<driver>'|null,
 *     'serverName' => 'localhost|127.0.0.1|<HTTP_HOST>',
 *     'port' => '<port>',
 *     'userName' => '<user_name>'|null,
 *     'password' => '<password>'|null,
 *     'databaseName' => '<database_name>'|null,
 *     'crypted' => true|false  // true if userName and password are crypted, otherwise false. false by default.
 * ]
 * 
 * <HTTP_HOST> is determined by $_SERVER['HTTP_HOST'].
 * <data_source_name> is a custom data source name. For example, insted of 'mysql', rename 'mysql' to 'db', to use $this->db and access to the MySQL database.
 * <HTTP_HOST> is the data source server name or IP (ex: localhost, 127.0.0.1).
 * <port> is the data source port.
 * <user_name> is the user name to connect to the data source. If user name is crypted (password must be crypted too), then "crypted" must be set to true.
 * <password> is the password to connect to the data source, associated with the user name. If password is crypted (user name must be crypted too), then "crypted" must be set to true.
 * <database_name> is the database name.
 */
return [
    'redis' => [
        'type' => 'redis',
        'driver' => null,
        'serverName' => '127.0.0.1',
        'port' => 6379,			//(optional) 6379 by default
        'userName' => null,
        'password' => null,
        'databaseName' => null,
        'crypted' => false,

        // Specific attributes
        'timeout' => 0,			//(optional) Float value in seconds. 0 by default for unlimited
        'reserved' => null,		//(optional) Should be NULL if retry_interval is specified
        'retryInterval' => 0,	//(optional) Int value in milliseconds
        'readTimeout' => 0		//(optional) Float value in seconds. 0 by default for unlimited)
    ],
    'pdo' => [
        'type' => 'pdo',
        'driver' => 'mysql',
        'serverName' => '127.0.0.1',
        'port' => 3306,
        'userName' => 'apifony',
        'password' => 'apifony',
        'databaseName' => 'apifony',
        'crypted' => false,

        // Specific attributes
        'options' => [
            \PDO::ATTR_EMULATE_PREPARES => false,               // Turn off emulation mode for "real" prepared statements
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,      // Turn on errors in the form of exceptions
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC  // Make the default fetch be an associative array
        ]
    ],
    'mysql' => [
        'type' => 'mysql',
        'driver' => 'mysql',
        'serverName' => '127.0.0.1',
        'port' => 3306,
        'userName' => 'apifony',
        'password' => 'apifony',
        'databaseName' => 'apifony',
        'crypted' => false
    ]/*,
    'postgresql' => [
        'type' => 'postgresql',
        'driver' => 'pgsql',
        'serverName' => '127.0.0.1',
        'port' => 5432,
        'userName' => 'apifony',
        'password' => 'apifony',
        'databaseName' => 'apifony',
        'crypted' => false
    ],
    'oracle' => [
        'type' => 'oracle',
        'driver' => 'oci',
        'serverName' => '127.0.0.1',
        'port' => 1521,
        'userName' => 'apifony',
        'password' => 'apifony',
        'databaseName' => 'apifony',
        'crypted' => false
    ],
    'mongodb' => [
        'type' => 'mongodb',
        'driver' => 'mongodb',
        'serverName' => '127.0.0.1',
        'port' => 27017,
        'userName' => 'apifony',
        'password' => 'apifony',
        'databaseName' => 'apifony',
        'crypted' => false
    ]*/
];
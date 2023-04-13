<?php

// Database::getPDO()
class Database
{
    /**
     * PDO object representing the database connection
     * @var PDO
     */
    private $dbh;

    /**
     * Static property (linked to the class) storing the unique object instance
     * @var Database
     */
    private static $instance;

    private function __construct()
    {
        $configData = parse_ini_file(__DIR__ . '/../config.ini');

        try {
            $this->dbh = new PDO(
                "mysql:host={$configData['DB_HOST']};dbname={$configData['DB_NAME']};charset=utf8",
                $configData['DB_USERNAME'],
                $configData['DB_PASSWORD'],
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING)
            );
        } catch (\Exception $exception) {
            echo 'Connection to database failed:' . PHP_EOL;
            echo $exception->getMessage() . PHP_EOL;
            echo $exception->getTraceAsString() . PHP_EOL;
            exit;
        }
    }

    /**
     * Returns the unique instance of the class
     * @return PDO
     */
    public static function getPDO()
    {
        if (empty(self::$instance)) {
            self::$instance = new Database();
        }
        return self::$instance->dbh;
    }
}

<?php

abstract class DB_Generic extends PHPUnit_Extensions_Database_TestCase
{
    // only instantiate pdo once for test clean-up/fixture load
    static private $pdo = null;

    // only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
    private $conn = null;

    final public function getConnection()
    {
        if ($this->conn === null) {
            try {
                if (self::$pdo == null) {
                    self::$pdo = new PDO('mysql:host=' . DATABASE_HOST . ';dbname=' . DATABASE_NAME, DATABASE_USERNAME, DATABASE_PASSWORD);
                }
                $this->conn = $this->createDefaultDBConnection(self::$pdo, DATABASE_NAME);
            } catch (PDOException $e) {
                print_r($e->getMessage());
            }
        }
        return $this->conn;
    }
}
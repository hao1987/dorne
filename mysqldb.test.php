<?php

/**
 * first test script after switching to PDO, main functions in class.mysqldb.php
 */
class Mysqldb_Test extends DB_Generic
{
    private static $mysqldb;

    public static function setUpBeforeClass()
    {
        self::$mysqldb = new mysqldb(DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME, DATABASE_HOST);
        self::$mysqldb->verbose = true;
    }

    public function setUp()
    {
        $conn = $this->getConnection(); //return createDefaultDBConnection
        $pdo = $conn->getConnection(); //interesting, return pdo object

// it's an agnoy that we can setup fixture(only data) base on the yaml but
// there is no way to specify schema on yaml, have to hardcode it for now
        $this->fixture = $this->getDataSet();
        foreach ($this->fixture->getTableNames() as $table) {
            $query = "CREATE TABLE IF NOT EXISTS `test`.`$table` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`email` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`username` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`password` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`ts_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = INNODB ;";
            $pdo->exec($query);
        }

        self::$mysqldb->pdo = $pdo;
        parent::setUp();
    }

    protected function getDataSet()
    {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet(
            dirname(__FILE__) . "/dataset.yml"
        );
    }

    public function testGetRowCount()
    {
        $this->assertEquals(4, $this->getConnection()->getRowCount('test_pdo'));
    }

    public function testInsert()
    {
        $insert_id = self::$mysqldb->query("insert into test_pdo (email, username, password) values ('more@test.com', 'testking', '123456')");
        $this->assertEquals(5, $this->getConnection()->getRowCount('test_pdo'), "Inserting failed");

        $id = self::$mysqldb->insert_id();
        $this->assertEquals($insert_id, $id);

        $queryTable = $this->getConnection()->createQueryTable(
            'test_pdo', 'SELECT email, username, password FROM test_pdo order by id DESC LIMIT 1'
        );
        $expected = array('email' => 'more@test.com',
            'username' => 'testking',
            'password' => '123456'
        );
        $this->assertEquals($expected, $queryTable->getRow(0));
    }

    public function testUpdate()
    {
        $affected_rows = self::$mysqldb->query("update test_pdo set email = 'winterfell@gmail.com'");

        $this->assertEquals(4, $this->getConnection()->getRowCount('test_pdo'), "Updating failed");
        $this->assertEquals(4, intval($affected_rows));

        $queryTable = $this->getConnection()->createQueryTable(
            'test_pdo', 'SELECT email FROM test_pdo order by id DESC LIMIT 1'
        );
        $this->assertEquals('winterfell@gmail.com', $queryTable->getValue(0, 'email'));
    }

    public function testDelete()
    {
        $affected_rows = self::$mysqldb->query("delete from test_pdo");
        $this->assertEquals(0, $this->getConnection()->getRowCount('test_pdo'), "Deleting failed");
        $this->assertEquals(4, intval($affected_rows));

        $affected_rows = self::$mysqldb->query("delete from test_pdo where id = 999");
        $this->assertEquals(0, intval($affected_rows));
    }

    public function testFetchList()
    {
        $res = self::$mysqldb->fetch_list("select email from test_pdo limit 3");
        $expected = array('john@gmail.com', 'kahleesi@gmail.com', null);
        $this->assertEquals($expected, $res);
        $this->assertCount(3, $res);

        $res = self::$mysqldb->fetch_list("select id, email from test_pdo");
        $expected = array('john@gmail.com', 'kahleesi@gmail.com', null, 'test@gmail.com');
        $this->assertEquals($expected, $res);
    }

    public function testFetchVar()
    {
        $res = self::$mysqldb->fetch_var("select username from test_pdo where id = 2");
        $this->assertEquals('kahleesi', $res);

        $res = self::$mysqldb->fetch_var("select username from test_pdo where id = 999");
        $this->assertFalse($res);

        $res = self::$mysqldb->fetch_var("select username from test_pdo");
        $username_pool = array('john1987', 'kahleesi', 'Stark', 'test@gmail.com');
        $this->assertContains($res, $username_pool);
    }

    public function tearDown()
    {
        $conn = $this->getConnection();
        $pdo = $conn->getConnection();
        $allTables = $this->getDataSet()->getTableNames();
        foreach ($allTables as $table) {
            if ($table == 'test_pdo') {
                $pdo->exec("DROP TABLE IF EXISTS `$table`;");
            }
        }
        $conn->close();

        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        self::$mysqldb->close();
    }
}

<?php

namespace chloe463\Blauwal;

class BlauwalDummyClass
{
    use Blauwal;

    public function __construct()
    {
        $connection_info = [
            'host'            => $_ENV['DB_HOST'],
            'port'            => $_ENV['DB_PORT'],
            'user'            => $_ENV['DB_USER'],
            'pass'            => $_ENV['DB_PASS'],
            'db_name'         => $_ENV['DB_NAME'],
            'collection_name' => $_ENV['COLLECTION']
        ];
        $options = [
            'ssl'                 => false,
            'connectionTimeoutMS' => 1000 * 1000
        ];
        $driver_options = [
            'allow_invalid_hostname' => false
        ];
        $this->init($connection_info, $options, $driver_options);
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}

class BlauwalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BlauwalDummyClass
     */
    protected $object;

    /**
     * Called before a test run
     * Insert some data into mongodb
     */
    public function setUp()
    {
        $this->object   = new BlauwalDummyClass();

        $test_documents = json_decode(file_get_contents(__DIR__ . '/test_documents.json'), true);
        $bulk           = new \MongoDB\Driver\Bulkwrite(['ordered' => true]);
        foreach ($test_documents as $doc) {
            $milliseconds      = (new \DateTime($doc['timeField']))->getTimeStamp() * 1000;
            $doc['created_at'] = new \MongoDB\BSON\UTCDateTime($milliseconds);
            $ids[]              = $bulk->insert($doc);
        }
        $uri = sprintf("mongodb://%s:%s@%s:%s/%s",
            $_ENV['DB_USER'],
            $_ENV['DB_PASS'],
            $_ENV['DB_HOST'],
            $_ENV['DB_PORT'],
            $_ENV['DB_NAME']
        );
        $dbh          = new \MongoDB\Driver\Manager($uri); 
        $write_result = $dbh->executeBulkWrite($_ENV['DB_NAME'].'.'.$_ENV['COLLECTION'], $bulk);
    }

    /**
     * Called after a test run
     * Remove all test documents
     */
    public function tearDown()
    {
        $bulk = new \MongoDB\Driver\BulkWrite();
        $bulk->delete(['blauwalTest' => true]);

        $uri = sprintf("mongodb://%s:%s@%s:%s/%s",
            $_ENV['DB_USER'],
            $_ENV['DB_PASS'],
            $_ENV['DB_HOST'],
            $_ENV['DB_PORT'],
            $_ENV['DB_NAME']
        );
        $dbh          = new \MongoDB\Driver\Manager($uri); 
        $write_result = $dbh->executeBulkWrite($_ENV['DB_NAME'].'.'.$_ENV['COLLECTION'], $bulk);
    }

    /**
     * chloe463\Blauwal\Blauwal::getConnectionInfo
     */
    public function testGetconnectioninfo()
    {
        $expected_result = [
            'host'            => $_ENV['DB_HOST'],
            'port'            => $_ENV['DB_PORT'],
            'user'            => $_ENV['DB_USER'],
            'pass'            => $_ENV['DB_PASS'],
            'db_name'         => $_ENV['DB_NAME'],
            'collection_name' => $_ENV['COLLECTION']
        ];
        $actual_result = $this->object->getConnectionInfo();

        $this->assertEquals($expected_result, $actual_result);
    }

    /**
     * chloe463\Blauwal\Blauwal::getOptions
     */
    public function testGetoptions()
    {
        $expected_result = [
            'ssl'                 => false,
            'connectionTimeoutMS' => 1000 * 1000
        ];
        $actual_result = $this->object->getOptions();

        $this->assertEquals($expected_result, $actual_result);
    }

    /**
     * chloe463\Blauwal\Blauwal::getDriverOptions
     */
    public function testGetdriveroptions()
    {
        $expected_result = [
            'allow_invalid_hostname' => false
        ];
        $actual_result = $this->object->getDriverOptions();

        $this->assertEquals($expected_result, $actual_result);
    }

    /**
     * chloe463\Blauwal\Blauwal::getDbName
     */
    public function testGetDbName()
    {
        $expected_result = $_ENV['DB_NAME'];
        $actual_result   = $this->object->getDbName();
        $this->assertEquals($expected_result, $actual_result);
    }

    /**
     * chloe463\Blauwal\Blauwal::getCollectionName
     */
    public function testGetcollectionname()
    {
        $expected_result = $_ENV['COLLECTION'];
        $actual_result   = $this->object->getCollectionName();
        $this->assertEquals($expected_result, $actual_result);
    }

    /**
     * chloe463\Blauwal\Blauwal::getDbh
     */
    public function testGetdbh()
    {
        $expected_result = null;
        $actual_result   = $this->object->getDbh();
        $this->assertEquals($expected_result, $actual_result);

        $this->object->connect();
        $expected_result = '\MongoDB\Driver\Manager';
        $actual_result   = $this->object->getDbh();
        $this->assertInstanceOf($expected_result, $actual_result);
        $this->object->disconnect();
    }

    /**
     * chloe463\Blauwal\Blauwal::getTarget
     */
    public function testGetTarget()
    {
        $expected_result = sprintf("%s.%s", $_ENV['DB_NAME'], $_ENV['COLLECTION']);
        $actual_result   = $this->object->getTarget();
        $this->assertEquals($expected_result, $actual_result);
    }

    /**
     * chloe463\Blauwal\Blauwal::init
     */
    public function testInit()
    {
        $connection_info = [
            'host'            => $_ENV['DB_HOST'],
            'port'            => $_ENV['DB_PORT'],
            'user'            => $_ENV['DB_USER'],
            'pass'            => $_ENV['DB_PASS'],
            'db_name'         => $_ENV['DB_NAME'],
            'collection_name' => $_ENV['COLLECTION']
        ];
        $options = [
            'ssl'                 => false,
            'connectionTimeoutMS' => 1000 * 1000
        ];
        $driver_options = [
            'allow_invalid_hostname' => false
        ];
        $this->object->init($connection_info, $options, $driver_options);

        $this->assertEquals($connection_info, $this->object->getConnectionInfo());
        $this->assertEquals($options, $this->object->getOptions());
        $this->assertEquals($driver_options, $this->object->getDriverOptions());
    }

    /**
     * chloe463\Blauwal\Blauwal::validateConnectionInfo
     */
    public function testValidateconnectioninfo()
    {
        $connection_info = [
            'host'            => $_ENV['DB_HOST'],
            'port'            => $_ENV['DB_PORT'],
            'user'            => $_ENV['DB_USER'],
            'pass'            => $_ENV['DB_PASS'],
            'db_name'         => $_ENV['DB_NAME'],
            'collection_name' => $_ENV['COLLECTION']
        ];
        try {
            $this->object->validateconnectioninfo($connection_info);
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            echo $e->getTraceAsString() . PHP_EOL;
            $this->fail();
        }
    }

    /**
     * chloe463\Blauwal\Blauwal::validateConnectionInfo
     */
    public function testValidateconnectioninfo_throwsException()
    {
        // Missing: collection_name
        $connection_info = [
            'host'            => $_ENV['DB_HOST'],
            'port'            => $_ENV['DB_PORT'],
            'user'            => $_ENV['DB_USER'],
            'pass'            => $_ENV['DB_PASS'],
            'db_name'         => $_ENV['DB_NAME']
        ];
        try {
            $this->object->validateconnectioninfo($connection_info);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertEquals(1, $e->getCode());
            $this->assertEquals('Some parameters are missing(collection_name)', $e->getMessage());
        }

        // Missing: db_name, collection_name
        $connection_info = [
            'host'            => $_ENV['DB_HOST'],
            'port'            => $_ENV['DB_PORT'],
            'user'            => $_ENV['DB_USER'],
            'pass'            => $_ENV['DB_PASS'],
        ];
        try {
            $this->object->validateconnectioninfo($connection_info);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertEquals(1, $e->getCode());
            $this->assertEquals('Some parameters are missing(db_name,collection_name)', $e->getMessage());
        }

        // Missing: pass, db_name, collection_name
        $connection_info = [
            'host'            => $_ENV['DB_HOST'],
            'port'            => $_ENV['DB_PORT'],
            'user'            => $_ENV['DB_USER'],
        ];
        try {
            $this->object->validateconnectioninfo($connection_info);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertEquals(1, $e->getCode());
            $this->assertEquals('Some parameters are missing(pass,db_name,collection_name)', $e->getMessage());
        }

        // Missing: user, pass, db_name, collection_name
        $connection_info = [
            'host'            => $_ENV['DB_HOST'],
            'port'            => $_ENV['DB_PORT'],
        ];
        try {
            $this->object->validateconnectioninfo($connection_info);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertEquals(1, $e->getCode());
            $this->assertEquals('Some parameters are missing(user,pass,db_name,collection_name)', $e->getMessage());
        }

        // Missing: port, user, pass, db_name, collection_name
        $connection_info = [
            'host'            => $_ENV['DB_HOST'],
        ];
        try {
            $this->object->validateconnectioninfo($connection_info);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertEquals(1, $e->getCode());
            $this->assertEquals('Some parameters are missing(port,user,pass,db_name,collection_name)', $e->getMessage());
        }

        // Missing: host, port, user, pass, db_name, collection_name
        $connection_info = [
        ];
        try {
            $this->object->validateconnectioninfo($connection_info);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertEquals(1, $e->getCode());
            $this->assertEquals('Some parameters are missing(host,port,user,pass,db_name,collection_name)', $e->getMessage());
        }
    }

    /**
     * chloe463\Blauwal\Blauwal::connect
     */
    public function testConnect()
    {
        $expected_result = '\MongoDB\Driver\Manager';
        $actual_result   = $this->object->connect();
        $this->assertInstanceOf($expected_result, $actual_result);
    }

    /**
     * chloe463\Blauwal\Blauwal::disconnect
     */
    public function testDisconnect()
    {
        $this->object->connect();
        $this->object->disconnect();
        $this->assertNull($this->object->getDbh());
    }

    /**
     * chloe463\Blauwal\Blauwal::buildUri
     */
    public function testBuilduri()
    {
        $expected_result = sprintf("mongodb://%s:%s@%s:%s/%s",
            $_ENV['DB_USER'],
            $_ENV['DB_PASS'],
            $_ENV['DB_HOST'],
            $_ENV['DB_PORT'],
            $_ENV['DB_NAME']
        );
        $actual_result   = $this->object->buildUri();
        $this->assertEquals($expected_result, $actual_result);
    }

    /**
     * chloe463\Blauwal\Blauwal::convert2Regex
     */
    public function testConvert2Regex()
    {
        $expected_class_name = '\MongoDB\BSON\Regex';

        // Pass only pattern
        $actual_result       = $this->object->convert2regex('foo');

        $this->assertInstanceOf($expected_class_name, $actual_result);
        $expected_pattern = 'foo';
        $expected_flags   = '';
        $this->assertEquals($expected_pattern, $actual_result->getPattern());
        $this->assertEquals($expected_flags, $actual_result->getFlags());

        // Pass pattern and flags
        $actual_result = $this->object->convert2regex('foo', 'i');

        $this->assertInstanceOf($expected_class_name, $actual_result);
        $expected_pattern = 'foo';
        $expected_flags   = 'i';
        $this->assertEquals($expected_pattern, $actual_result->getPattern());
        $this->assertEquals($expected_flags, $actual_result->getFlags());

        // Pass pattern with delimiter
        $actual_result = $this->object->convert2regex('/foo/', 'i');

        $this->assertInstanceOf($expected_class_name, $actual_result);
        $expected_pattern = 'foo';
        $expected_flags   = 'i';
        $this->assertEquals($expected_pattern, $actual_result->getPattern());
        $this->assertEquals($expected_flags, $actual_result->getFlags());
    }

    /**
     * chloe463\Blauwal\Blauwal::convert2UTCDateTime
     */
    public function testConvert2UtcDateTime()
    {
        $expected_class_name = '\MongoDB\BSON\UTCDateTime';

        $actual_result = $this->object->convert2UTCDateTime();
        $this->assertInstanceOf($expected_class_name, $actual_result);

        $time          = '2017-01-08 12:00:00';
        $milliseconds  = (new \DateTime($time))->getTimeStamp() * 1000;
        $actual_result = $this->object->convert2UTCDateTime($time);
        $this->assertInstanceOf($expected_class_name, $actual_result);
        $this->assertEquals($milliseconds, $actual_result->__toString());

        $time          = '2017/01/08 12:00:00';
        $milliseconds  = (new \DateTime($time))->getTimeStamp() * 1000;
        $actual_result = $this->object->convert2UTCDateTime($time);
        $this->assertInstanceOf($expected_class_name, $actual_result);
        $this->assertEquals($milliseconds, $actual_result->__toString());
    }

    /**
     * chloe463\Blauwal\Blauwal::buildWriteConcern
     */
    public function testBuildWriteConcern()
    {
        $expected_class_name = '\MongoDB\Driver\WriteConcern';

        // Pass no arguments
        $actual_result       = $this->object->buildWriteConcern();
        $this->assertInstanceOf($expected_class_name, $actual_result);
        $this->assertEquals(\MongoDB\Driver\WriteConcern::MAJORITY, $actual_result->getW());
        $this->assertEquals(1000000, $actual_result->getWtimeout());
        $this->assertEquals(true, $actual_result->getJournal());

        // Pass 3 arguments
        $actual_result       = $this->object->buildWriteConcern(1, 1000, false);
        $this->assertInstanceOf($expected_class_name, $actual_result);
        $this->assertEquals(1, $actual_result->getW());
        $this->assertEquals(1000, $actual_result->getWtimeout());
        $this->assertEquals(false, $actual_result->getJournal());
    }

    /**
     * chloe463\Blauwal\Blauwal::insert
     */
    public function testInsert()
    {
        $new_document = [
            ['blauwalTest' => true, 'name' => 'abc', 'subject' => 'Science', 'score' => 80, 'created_at' => $this->object->convert2UTCDateTime()],
        ];

        $actual_result = null;
        try {
            $write_concern = $this->object->buildWriteConcern();
            $actual_result = $this->object->insert($new_document);
        } catch (\Exception $e) {
            echo $e->getCode() . PHP_EOL;
            echo $e->getMessage() . PHP_EOL;
            $this->fail();
        }

        $this->assertEquals(1, count($actual_result));
        $this->assertInstanceOf('\MongoDB\Driver\WriteResult', reset($actual_result));
        $this->assertEquals(1, reset($actual_result)->getInsertedCount());
    }

    /**
     * chloe463\Blauwal\Blauwal::batchInsert
     */
    public function testBatchinsert()
    {
        $new_documents = [
            ['blauwalTest' => true, 'name' => 'abc', 'subject' => 'Science', 'score' => 80, 'created_at' => $this->object->convert2UTCDateTime()],
            ['blauwalTest' => true, 'name' => 'abc', 'subject' => 'Social Study', 'score' => 80, 'created_at' => $this->object->convert2UTCDateTime()],
            ['blauwalTest' => true, 'name' => 'abc', 'subject' => 'French', 'score' => 80, 'created_at' => $this->object->convert2UTCDateTime()],
        ];

        $actual_result = null;
        try {
            $write_concern = $this->object->buildWriteConcern();
            $actual_result = $this->object->batchInsert($new_documents);
        } catch (\Exception $e) {
            echo $e->getCode() . PHP_EOL;
            echo $e->getMessage() . PHP_EOL;
            $this->fail();
        }

        $this->assertInstanceOf('\MongoDB\Driver\WriteResult', $actual_result);
        $this->assertEquals(3, $actual_result->getInsertedCount());
    }

    /**
     * chloe463\Blauwal\Blauwal::find
     */
    public function testFind()
    {
        $filter = [
            'name' => 'foo'
        ];
        $fields = [
            'name'    => 1,
            'subject' => 1,
            'score'   => 1
        ];
        $actual_result = $this->object->find($filter, $fields);
        $this->assertCount(3, $actual_result);
        foreach ($actual_result as $item) {
            $this->assertEquals('foo', $item->name);
            $this->assertRegExp('/Japanese|Math|English/', $item->subject);
            $this->assertGreaterThan(0, $item->score);
        }
    }

    /**
     * chloe463\Blauwal\Blauwal::mergeProjectionAndOption
     */
    public function testMergeProjectionAndOption()
    {
        $fields = [
            'name'    => 1,
            'subject' => 1,
            'score'   => 1
        ];
        $options = [
            'limit' => 1
        ];

        $expected_result = [
            'projection' => [
                'name'    => 1,
                'subject' => 1,
                'score'   => 1
            ],
            'limit' => 1
        ];

        $this->assertEquals($expected_result, $this->object->mergeProjectionAndOption($fields, $options));

        $fields = [
            'name'    => 1,
            'subject' => 1,
            'score'   => 1
        ];
        $options = [
            'limit' => 1,
            'projection' => [
                'created_at' => 1
            ]
        ];

        $expected_result = [
            'projection' => [
                'name'       => 1,
                'subject'    => 1,
                'score'      => 1,
                'created_at' => 1
            ],
            'limit' => 1
        ];

        $this->assertEquals($expected_result, $this->object->mergeProjectionAndOption($fields, $options));
    }

    /**
     * chloe463\Blauwal\Blauwal::update
     */
    public function testUpdate()
    {
        $filter = [
            'name' => 'bar'
        ];
        $update_values = [
            '$inc' => [
                'score' => 10,
                'metrics.orders' => 1
            ]
        ];
        $options = [
            'multi' => true
        ];

        $actual_result = null;
        try {
            $actual_result = $this->object->update($filter, $update_values, $options);
        } catch (\Exception $e) {
            echo $e->getCode() . PHP_EOL;
            echo $e->getMessage() . PHP_EOL;
            $this->fail();
        }
        $this->assertInstanceOf('\MongoDB\Driver\WriteResult', $actual_result);
        // $this->assertEquals(4, $actual_result->getModifiedCount());
    }

    /**
     * chloe463\Blauwal\Blauwal::remove
     */
    public function testRemove()
    {
        $filter = [
            'name' => 'bar'
        ];
        $options = [];

        $actual_result = null;
        try {
            $actual_result = $this->object->remove($filter, $options);
        } catch (\Exception $e) {
            echo $e->getCode() . PHP_EOL;
            echo $e->getMessage() . PHP_EOL;
            $this->fail();
        }
        $this->assertInstanceOf('\MongoDB\Driver\WriteResult', $actual_result);
        // $this->assertEquals(4, $actual_result->getDeletedCount());
    }
}

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
        $this->init($connection_info);
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
     * chloe463\blauwal\blauwal::getconnectioninfo
     */
    public function testGetconnectioninfo()
    {
    }

    /**
     * chloe463\blauwal\blauwal::getoptions
     */
    public function testGetoptions()
    {
    }

    /**
     * chloe463\blauwal\blauwal::getdriveroptions
     */
    public function testGetdriveroptions()
    {
    }

    /**
     * chloe463\blauwal\blauwal::getdbname
     */
    public function testGetdbname()
    {
    }

    /**
     * chloe463\blauwal\blauwal::getcollectionname
     */
    public function testGetcollectionname()
    {
    }

    /**
     * chloe463\blauwal\blauwal::getdbh
     */
    public function testGetdbh()
    {
    }

    /**
     * chloe463\blauwal\blauwal::gettarget
     */
    public function testGettarget()
    {
    }

    /**
     * chloe463\blauwal\blauwal::init
     */
    public function testInit()
    {
    }

    /**
     * chloe463\blauwal\blauwal::validateconnectioninfo
     */
    public function testValidateconnectioninfo()
    {
    }

    /**
     * chloe463\blauwal\blauwal::connect
     */
    public function testConnect()
    {
    }

    /**
     * chloe463\blauwal\blauwal::disconnect
     */
    public function testDisconnect()
    {
    }

    /**
     * chloe463\blauwal\blauwal::builduri
     */
    public function testBuilduri()
    {
    }

    /**
     * chloe463\blauwal\blauwal::convert2regex
     */
    public function testConvert2regex()
    {
    }

    /**
     * chloe463\blauwal\blauwal::convert2utcdatetime
     */
    public function testConvert2utcdatetime()
    {
    }

    /**
     * chloe463\blauwal\blauwal::buildwriteconcern
     */
    public function testBuildwriteconcern()
    {
    }

    /**
     * chloe463\blauwal\blauwal::insert
     */
    public function testInsert()
    {
    }

    /**
     * chloe463\blauwal\blauwal::batchinsert
     */
    public function testBatchinsert()
    {
    }

    /**
     * chloe463\blauwal\blauwal::find
     */
    public function testFind()
    {
    }

    /**
     * chloe463\blauwal\blauwal::mergeprojectionandoption
     */
    public function testMergeprojectionandoption()
    {
    }

    /**
     * chloe463\blauwal\blauwal::update
     */
    public function testUpdate()
    {
    }

    /**
     * chloe463\blauwal\blauwal::remove
     */
    public function testRemove()
    {
    }
}

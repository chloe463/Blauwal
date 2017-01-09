<?php

require_once __DIR__ . '/vendor/autoload.php';

use chloe463\Blauwal\Blauwal;

class BlauwalDummyClass
{
    use Blauwal;

    public function __construct()
    {
        $connection_info = [
            'host'            => 'localhost',
            'port'            => 27017,
            'user'            => 'blauwal',
            'pass'            => 'password',
            'db_name'         => 'test',
            'collection_name' => 'blauwal_test'
        ];
        $this->init($connection_info);
    }

    public function fetch()
    {
        $queries = [
            'score'      => [ '$gte' => 96 ],
            'name'       => $this->convert2Regex('foo'),
            'created_at' => [
                '$gte' => $this->convert2UTCDateTime('2017-01-07 00:00:00'),
                '$lt'  => $this->convert2UTCDateTime('2017-01-08 00:00:00'),
            ]
        ];
        $fields = [
            '_id'        => 1,
            'name'       => 1,
            'score'      => 1,
            'created_at' => 1
        ];

        $documents = [];
        try {
            $documents = $this->find($queries, $fields, [], $this->buildReadPreference());
        } catch (\Exception $e) {
            echo $e->getCode() . PHP_EOL;
            echo $e->getMessage() . PHP_EOL;
            throw $e;
        }
        print_r($documents);
    }

    public function store()
    {
        $now = (new \DateTime())->getTimeStamp();
        $name = md5($now);
        $new_docs = [
            ['insertTest' => true, 'name' => $name, 'subject' => 'Japanese', 'score' => rand(50, 100), 'created_at' => $this->convert2UTCDateTime()],
            ['insertTest' => true, 'name' => $name, 'subject' => 'Math', 'score' => rand(50, 100), 'created_at' => $this->convert2UTCDateTime()],
            ['insertTest' => true, 'name' => $name, 'subject' => 'English', 'score' => rand(50, 100), 'created_at' => $this->convert2UTCDateTime()],
        ];

        $write_result = null;
        try {
            $ordered       = false;
            $write_concern = $this->buildWriteConcern();
            $write_result  = $this->insert($new_docs, $ordered, $write_concern);
            // $write_result  = $this->batchInsert($new_docs, $ordered, $write_concern);
        } catch (\Exception $e) {
            echo $e->getCode() . PHP_EOL;
            echo $e->getMessage() . PHP_EOL;
            throw $e;
        }
        var_dump($write_result);
    }

    public function edit()
    {
        $query = [
            'insertTest' => true
        ];
        $set = [
            '$set' => [
                'insertTest' => false
            ]
        ];
        $write_concern = $this->buildWriteConcern();

        $write_result  = null;

        try {
            $write_result = $this->update($query, $set, ['multi' => true], $write_concern);
        } catch (\Exception $e) {
            echo $e->getCode() . PHP_EOL;
            echo $e->getMessage() . PHP_EOL;
            throw $e;
        }
        var_dump($write_result);
    }

    public function delete()
    {
        $query = [
            'insertTest' => false
        ];
        $write_concern = $this->buildWriteConcern();
        $write_result  = null;

        try {
            $write_result = $this->remove($query);
        } catch (\Exception $e) {
            echo $e->getCode() . PHP_EOL;
            echo $e->getMessage() . PHP_EOL;
            throw $e;
        }
        var_dump($write_result);
    }
}

$blauwal_dummy = new BlauwalDummyClass();
$blauwal_dummy->fetch();
$blauwal_dummy->store();
$blauwal_dummy->edit();
$blauwal_dummy->delete();

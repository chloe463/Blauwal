# Blauwal

## Summary
A wrapper class(trait) of MongoDB\Driver

## Install

```bash
$ composer require chloe463\blauwal
```

## Usage


```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use chloe463\Blauwal\Blauwal;

class BlauwalDummyClass
{
    use Blauwal;

    public function __construct()
    {
        $connection_info = [
            'host'            => DB_HOST,
            'port'            => DB_PORT,
            'user'            => DB_USER,
            'pass'            => DB_PASS,
            'db_name'         => DB_NAME,
            'collection_name' => COLLECTION
        ];
        $this->init($connection_info);
    }

    public function doSomething()
    {
        /**
         * Find
         */
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
            $documents = $this->find($queries, $fields);
        } catch (\Exception $e) {
            // Handle exception
        }

        /**
         * Insert
         */
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
            // Handle exception
        }

        /**
         * Update
         */
        $query = ['insertTest' => true];
        $set   = [
            '$set' => [
                'insertTest' => false
            ]
        ];
        $write_result  = null;

        try {
            $write_result = $this->update($query, $set, ['multi' => true], $this->buildWriteConcern());
        } catch (\Exception $e) {
            // Handle exception
        }

        /**
         * Remove
         */
        $query = [
            'insertTest' => false
        ];
        $options       = [];
        $write_concern = $this->buildWriteConcern();
        $write_result  = null;

        try {
            $write_result = $this->remove($query, $options, $write_concern);
        } catch (\Exception $e) {
            // Handle exception
        }
    }
}
```


<?php
/**
 * chloe463\Blauwal\Blauwal
 *
 * A wrapper class(trait) of MongoDB\Driver
 */

namespace chloe463\Blauwal;

trait Blauwal
{
    /**
     * @var array
     */
    protected $connection_info;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $driver_options;

    /**
     * @var string
     */
    protected $db_name;

    /**
     * @var string
     */
    protected $collection_name;

    /**
     * @var string  $target - database.collection
     */
    protected $target;


    /**
     * @var \MongoDB\Driver\Manager
     */
    protected $dbh;

    /**
     * Setters and getters
     */
    public function getConnectionInfo()
    {
        return $this->connection_info;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getDriverOptions()
    {
        return $this->driver_options;
    }

    public function getDbName()
    {
        return $this->db_name;
    }

    public function getCollectionName()
    {
        return $this->collection_name;
    }

    public function getDbh()
    {
        return $this->dbh;
    }

    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Initialize
     */
    public function init($connection_info, $options = [], $driver_options = [])
    {
        $this->validateConnectionInfo($connection_info);
        $this->connection_info = $connection_info;
        $this->options         = $options;
        $this->driver_options  = $driver_options;

        $this->db_name         = $connection_info['db_name'];
        $this->collection_name = $connection_info['collection_name'];
        $this->target          = sprintf("%s.%s", $this->db_name, $this->collection_name);

        return;
    }

    /**
     * Validate connection info
     *
     * @param   array   $connection_info
     *
     * @throws  chloe463\Blauwal\Blauwal::Exception if some parameters are missing
     */
    public function validateConnectionInfo($connection_info)
    {
        $essential_keys = ['host', 'port', 'user', 'pass', 'db_name', 'collection_name'];
        $missing_keys   = [];
        foreach ($essential_keys as $key) {
            if (!isset($connection_info[$key])) {
                $missing_keys[] = $key;
            }
        }

        if (!empty($missing_keys)) {
            throw new Exception(sprintf("Some parameters are missing(%s)", implode($missing_keys, ',')), Exception::PARAMETER_ERROR);
        }

        return;
    }

    /**
     * Instantiate \MongoDB\Driver\Manager
     *
     * @return  \MongoDB\Driver\Manager
     */
    public function connect()
    {
        if (!is_a($this->dbh, '\MongoDB\Driver\Manager')) {
            $this->dbh = new \MongoDB\Driver\Manager($this->buildUri(), $this->getOptions(), $this->getDriverOptions());
        }

        return $this->dbh;
    }

    /**
     * Disconnect
     */
    public function disconnect()
    {
        $this->dbh = null;
    }


    /**
     * Build URI
     */
    public function buildUri()
    {
        return sprintf("mongodb://%s:%s@%s:%s/%s",
            $this->connection_info['user'],
            $this->connection_info['pass'],
            $this->connection_info['host'],
            $this->connection_info['port'],
            $this->connection_info['db_name']
        );
    }

    /**
     * Instantiate \MongoDB\BSON\Regex
     */
    public function convert2Regex($regex, $flags = '')
    {
        if (preg_match('/^\/(.*)\/$/', $regex, $matches)) {
            return new \MongoDB\BSON\Regex($matches[1], $flags);
        }
        return new \MongoDB\BSON\Regex($regex, $flags);
    }

    /**
     * Instantiate \MongoDB\BSON\UTCDateTime
     *
     * @param   string  $time
     * Time format is YYYY-MM-DD hh:mm:ss, YYYY/MM/DD hh:mm:ss or milliseconds from the UNIX epoch
     */
    public function convert2UTCDateTime($time = '')
    {
        if (preg_match('/[0-9]{4}[\-\/][0-9]{2}[\-\/][0-9]{2}\s[0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}/', $time, $matches)) {
            $milliseconds = (new \DateTime($time))->getTimeStamp() * 1000;
            return new \MongoDB\BSON\UTCDateTime($milliseconds);
        }
        if ($time === '') {
            $milliseconds = (new \DateTime())->getTimeStamp() * 1000;
            return new \MongoDB\BSON\UTCDateTime($milliseconds);
        }
        return new \MongoDB\BSON\UTCDateTime($time);
    }

    /**
     * Build \MonboDB\Driver\WriteConcern to pass \MongoDB\Driver\executeBulkWrite
     */
    public function buildWriteConcern($w = \MongoDB\Driver\WriteConcern::MAJORITY, $wtimeout = 1000000, $journal = true)
    {
        return new \MongoDB\Driver\WriteConcern($w, $wtimeout, $journal);
    }

    /**
     * Execute insert.
     * Insert record one by one.
     *
     * @param   array   $new_documents
     * @param   boolean $ordered
     * @param   \MongoDB\Driver\WriteConcern    $write_concern
     */
    public function insert($new_documents, $ordered = true, \MongoDB\Driver\WriteConcern $write_concern = null)
    {
        $ids           = [];
        $write_results = [];

        foreach ($new_documents as $doc) {
            $bulk            = new \MongoDB\Driver\Bulkwrite(['ordered' => $ordered]);
            $ids[]           = $bulk->insert($doc);
            $write_results[] = $this->connect()->executeBulkWrite($this->getTarget(), $bulk, $write_concern);
        }

        $this->disconnect();

        return $write_results;
    }

    /**
     * Execute insert.
     * Insert big array once.
     *
     * @param   array   $new_documents
     * @param   boolean $ordered
     * @param   \MongoDB\Driver\WriteConcern    $write_concern
     */
    public function batchInsert($new_documents, $ordered = true, \MongoDB\Driver\WriteConcern $write_concern = null)
    {
        $ids          = [];
        $write_result = null;

        foreach ($new_documents as $doc) {
            $bulk     = new \MongoDB\Driver\Bulkwrite(['ordered' => $ordered]);
            $ids[]    = $bulk->insert($doc);
        }
        $write_result = $this->connect()->executeBulkWrite($this->getTarget(), $bulk, $write_concern);

        $this->disconnect();

        return $write_result;
    }

    /**
     * Execute find and return documents
     *
     * @param   array   $filter
     * @param   array   $options
     * @param   int     $read_preference_mode
     *
     * @return  array
     */
    public function find($filter, $fields = [], $options = [], $read_preference_mode = \MongoDB\Driver\ReadPreference::RP_PRIMARY)
    {
        $query          = new \MongoDB\Driver\Query($filter, $this->mergeProjectionAndOption($fields, $options));
        $read_reference = new \MongoDB\Driver\ReadPreference($read_preference_mode);
        $cursor         = $this->connect()->executeQuery($this->getTarget(), $query, $read_reference);

        $this->disconnect();

        return $cursor->toArray();
    }

    /**
     * Merge projection and other options
     *
     * @param   array   $fields
     * @param   array   $options
     *
     * @return  array   $options
     */
    public function mergeProjectionAndOption($fields, $options)
    {
        if (isset($options['projection'])) {
            foreach ($fields as $key => $value) {
                $options['projection'][$key] = $value;
            }
            return $options;
        }

        $options['projection'] = $fields;
        return $options;
    }

    /**
     * Execute update
     *
     * @param   array   $filter
     * @param   array   $update_values
     * @param   array   $options
     * @param   \MongoDB\Driver\WriteConcern    $write_concern
     */
    public function update($filter, $update_values, $options = [], \MongoDB\Driver\WriteConcern $write_concern = null)
    {
        $set = [
            '$set' => $update_values
        ];
        $bulk = new \MongoDB\Driver\BulkWrite();
        $bulk->update($filter, $set, $options);
        $write_result = $this->connect()->executeBulkWrite($this->getTarget(), $bulk, $write_concern);

        $this->disconnect();

        return $write_result;
    }

    /**
     * Execute remove
     *
     * @param   array   $filter
     * @param   array   $options
     * @param   \MongoDB\Driver\WriteConcern    $write_concern
     */
    public function remove($filter, $options = [], \MongoDB\Driver\WriteConcern $write_concern = null)
    {
        $bulk = new \MongoDB\Driver\BulkWrite();
        $bulk->delete($filter, $options);
        $write_result = $this->connect()->executeBulkWrite($this->getTarget(), $bulk, $write_concern);

        $this->disconnect();

        return $write_result;
    }
}

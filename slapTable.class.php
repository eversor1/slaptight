<?php
class slapTable {
    public static $instance;
    var $tableName;
    var $db;
    var $pkey; //holds the primary key
    var $fields;

    /**
    * This constructs a new table object, first by getting information
    * from the database by describing the table structure, and from that
    * pulling out the primary key, and any auto_increment fields
    *
    * @param string $tableName
    */
    public function __construct($tableName) {
        $this->tableName = $tableName;
        $this->db = db::getInstance();
        //lets get some info on this table.
        $tableDesc = $this->db->query("DESC ".$tableName);
        foreach ($tableDesc as $key=>$description) {
            if (($description['Key'] == "PRI") && ($description['Extra'] == "auto_increment")) {
                $this->pkey = $description['Field'];
            }
            $this->fields[] = $description['Field'];
        }
    }

    /** 
    * function to check this table for a specific field name
    *
    * @param string $fieldName
    * @return bool true
    */
    public function hasField($fieldName) {
        foreach($this->fields as $field) {
            if ($field == $fieldName) {
                return true;
            }
        }
        return false;
    }

    /*
    * returns the name of the primary key for this table.
    *
    * @return string $this->pkey
    */
    public function getPKey() {
        return $this->pkey;
    }

    /**
    * Singleton control. If object with specified name does not exist, 
    * it will be created and returned.
    * If it does exist the pre-created object will be returned.
    * I guess we are assuming that your table structure isn't changing 
    * between queries :)
    *
    * @param  string $instanceName -- Name of object to return.
    * @return object $instance
    */
    public function getTable($tableName="default") {
        if (!isset(self::$instance[$tableName])) {
            $object= __CLASS__;
            self::$instance[$tableName] = new $object($tableName);
        }
        return self::$instance[$tableName];
    }
}

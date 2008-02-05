<?php
//SlapTight library.

include 'db.class.php';
include 'slapTable.class.php';
include 'slapRow.class.php';

class slapTight implements Iterator {
    //Most variabels will be prefixed strangely in here as to avoid 
    //Potential conflicts with table column names;
    public static $instance;
    var $instanceName;
    var $table;
    var $db;
    var $explainedResult;
    var $query;
    var $fields;
    var $rows;
    var $live = false;

    /**
    * This function initializes the slapTight class for the specified query
    * and acts as a factory for the slaprow object, producing an instantiated
    * slapRow object for each row returned from your query.
    * The live option exists to specifiy that the selection of data from the table
    * be retireved in real-time.
    *
    * @param  string $instanceName
    * @param  string $query 
    * @param  bool   $alive -- This allows live data retrival queries (see documentation)
    * @return object $this -- return the slapright object. iteration of results is supported.
    */
    public function select($instanceName="default", $query, $alive=false) {
        if (!isset(self::$instance[$instanceName])) {
            $object= __CLASS__;
            self::$instance[$instanceName] = new $object($instanceName, $query, $alive);
        }
        //return self::$instance[$instanceName]->getRows();
        return self::$instance[$instanceName];
    }

    /**
    * This function returns a named instance of the slapTight object for your query
    * if for some reason you may need to get at the actual query object. (none of 
    * which I can think of right now)
    *
    * @param string $instanceName
    * @return object(slaptight) $instance
    */
    public function getInstance($instanceName) {
        if (isset(self::$instance[$instanceName])) {
            return self::$instance[$instanceName];
        }
        return false;
    }

    /**
    * The slapTight constructor, should never be publicly called.
    *
    * @param string $instanceName
    * @param string $query
    * @param bool   $alive
    */
    public function __construct($instanceName, $query, $alive=false) {
        if ($alive === true) {
            $this->live = true;
        }
        $this->instanceName = $instanceName;
        $this->query = $query;
        $this->db = db::getInstance();
        if (strtolower(substr($query, 0, 6)) != "select") {
            die("This query is not a select query. slapTight::select only supports select queries.");
        }
        //first we need to find the tables,... easiset way to do this is have mysql explain the query for us.
        $explainSql = "EXPLAIN ".$query;
        $this->explainedResult = $this->db->query($explainSql);
        foreach ($this->explainedResult as $key=>$explanationRow) {
            $this->table[$explanationRow['table']] = slapTable::getTable($explanationRow['table']);
        }
        //we now need to modify the query to make sure we can see the primary keys that come off of each table.
        $newQuery = $this->addKeysToQuery();
        $result = $this->populate($newQuery);
    }

    /**
    * This function maps the returned fileds from a query onto their respected tables,
    * creates a slapRow object for each row returned from the query, and populates it 
    * with the data returned from the query
    *
    * @param  string $query
    */
    private function populate($query) {
        //lets actually preform the query and get a basic data load, mainly for structure. 
        $result = $this->db->query($query);
        //lets map out the fields in the result before we populate the row objects.
        $testRow = $result[0];
        foreach ($testRow as $fieldName=>$fieldData) {
            //lets find the associated table
            $len = count($this->fields);
            $this->fields[$len]['name'] = $fieldName;
            foreach ($this->table as $tableName=>$table) {
                if ($table->hasField($fieldName)) {
                    $this->fields[$len]['table'] = $tableName;
                }
            }
        }
        //create and populate a slapRow object for each row returned.
        foreach($result as $row) {
            $len = count($this->rows);
            $this->rows[$len] = new slapRow($this->instanceName);
            
            foreach ($this->fields as $field) {
                if (substr($field['name'], -4) == "_PRI") {
                    $tableName = substr($field['name'], 0, -4);
                    $this->rows[$len]->registerPKey($tableName, $this->table[$tableName]->getPKey(), $row[$field['name']]);
                } else {
                    $this->rows[$len]->setValue($field['name'], $field['table'], $row[$field['name']]);
                }
            }
        }
    }

    /** 
    * function that just returns the array of row objects 
    * that resulted from the query
    * 
    * @return array $rows
    */
    public function getRows() {
        return $this->rows;
    }

    /**
    * This is a small text parsing function that appends a request for the 
    * primary key column, for each table involved, as a known name.
    * This is used to stamp the object with an accountable row. 
    *
    * @return string $newQuery
    */
    private function addKeysToQuery() {
        //lets move through the tables for this query and get the keys;
        foreach ($this->table as $name=>$tableObj) {
            $columns .= ", ".$name.".".$tableObj->getPKey()." as ".$name."_PRI";
        }
        $pre = substr($this->query, 0, (strpos(strtolower($this->query), "from") - 1));
        $post = substr($this->query, (strpos(strtolower($this->query), "from") - 1));
        $newQuery = $pre.$columns.$post;
        return $newQuery;
    }

    /**
    * function to turn the query object alive (realtime requests).
    */
    public function alive() {
        $this->live = true;
    }
    
    /**
    * function to make the query object dead (no realtime requests).
    */
    public function dead() {
        $this->live = false;
    }

    /*****************************************************/

    public function rewind() {
        reset($this->rows);
    }

    public function current() {
        $var = current($this->rows);
        return $var;
    }

    public function key() {
        $var = key($this->rows);
        return $var;
    }

    public function next() {
        $var = next($this->rows);
        return $var;
    }

    public function valid() {
        $var = $this->current() !== false;
        return $var;
    }

}

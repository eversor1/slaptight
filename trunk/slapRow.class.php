<?
class slapRow {
    var $db;
    var $parentQueryName;
    var $parentQuery;
    var $rowData;
    var $fields;
    var $fieldTable;
    var $table;
    var $iteration;

    /** 
    * slapRow constructor
    * sets the parent query name, and the gets db object
    */
    public function __construct($parentQueryName) {
        $this->parentQueryName = $parentQueryName;
        $this->db = db::getInstance();
    }

    /**
    * This function is used only for setting the initial data for this particular row.
    *
    * @param string $name
    * @param mixed $value
    **/
    public function setValue($fieldName, $table, $fieldValue) {
        $this->rowData[$fieldName] = $fieldValue;
        $this->fieldTable[$fieldName] = $table;
        $len = count($this->fields);
        $this->fields[$len]['name'] = $fieldName;
        $this->fields[$len]['table'] = $table;
        $this->fields[$len]['value'] = $fieldValue;
    }

    /**
    * registers the primary key for this row, for a specific table.
    *
    * @param string $tableName
    * @param string $primaryKeyName
    * @param string $primaryKeyValue
    */
    public function registerPKey($table, $pkeyName, $pkeyValue) {
        $this->table[$table]['pkeyName'] = $pkeyName;
        $this->table[$table]['pkeyValue'] = $pkeyValue;
    }

    /**
    * This is the magic get function that will return the value for the 
    * specified column name, or if it is live, it will will kick off a query 
    * to get the latest value for the requested field.
    *
    * @param  string $name
    * @return string $rowData;
    */
    public function __get($name) {
        //we need to check to see if LIVE is turned on on this query. 
        if (!isset($this->parentQuery)) {
            $this->parentQuery = slapTight::getInstance($this->parentQueryName);
        }
        if ($this->parentQuery->live) {
            //lets find the table that the field name is a part of.
            $table = $this->fieldTable[$name];
            //assuming that the pkey hasn't changed lets get the value directly from the database!
            $sql = "SELECT `$name` FROM `$table` WHERE `".$this->table[$table]['pkeyName']."` = '".$this->table[$table]['pkeyValue']."';";
            return $this->db->queryField($sql);
        } else {
            return $this->rowData[$name];
        }
    }

    /**
    * This is the magic set function that really does the work of slaptight.
    * This allows values that are being set on our slapRow objects to modify 
    * the table that they come from in real time. 
    *
    * @param string $name
    * @param string $value
    */
    public function __set($name, $value) {
        if (!isset($this->parentQuery)) {
            $this->parentQuery = slapTight::getInstance($parentQueryName);
        }
        $table = $this->fieldTable[$name];
        $pkey = $this->table[$table]['pkeyName'];
        if ($pkey == $name) {
            die("Cannot set the primary key to a different value");
        }
        $sql = "UPDATE `".$table."` SET `".$name."`='".$value."' WHERE `".$this->table[$table]['pkeyName']."`='".$this->table[$table]['pkeyValue']."';";
        $this->rowData[$name] = $value;
        foreach ($this->fields as $key=>$field) {
            if ($field['name'] == $name) {
                $this->fields[$key]['value'] = $value;
            }
        }
        $this->db->update($sql);
    }
}

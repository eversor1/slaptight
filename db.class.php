<?
include 'config.class.php';
class db {
    public static $instance;
    var $instanceName;
    var $db;
    var $hostname;
    var $username;
    var $password;
    var $database;
    var $config;
    

    function __construct($instanceName, $configName=null) {
        //populate the needed connection information from the config.ini file
        $this->instanceName = $instanceName;
        $config = config::getInstance($configName);
        $this->hostname = $config->get('hostname');
        $this->username = $config->get('username');
        $this->password = $config->get('password');
        $this->database = $config->get('database');
        $this->db = mysql_connect($this->hostname, $this->username, $this->password) or die("There was an error conntecting to the database:".$this->database." on server: ".$this->hostname." for user:".$this->username);

        $selectedDB = mysql_select_db($this->database, $this->db) or die("There was an error selecting the database");
    }

    /**
    * The query function is to provide a easy and uniform interface to pass 
    * mysql querys to the system. the selected information will be returned 
    * in a mnult-dimenstional array
    *
    * @param string $sql
    * @return array $result
    **/
    function query($sql) {
        $result = mysql_query($sql) or die("There is an error in the SQL query");
        while ($row = mysql_fetch_assoc($result)) {
            $return[] = $row;
        }
        return $return;
    }

    /**
    * Function that will execute a query to the database without returning anything. 
    *
    * @param string $sql
    **/
    function update($sql) {
        mysql_query($sql) or die("There was an error in the SQL query");
    }

    /**
    * This function is the same as the update function except for insert statements.
    * This function will return any auto-generated id associated with the inserted row.
    *
    * @param string $sql
    * @return int $lid //last insert id
    */
    function insert($sql) {
        mysql_query($sql) or die("There was an error in the SQL query");
        $lid = mysql_insert_id();
        return $lid;
    }

    /**
    * queryArray is the default query behavior. The query is comepleted and the 
    * result is returned in a multi-dimensional array. Rows first, each row
    * then contains the columns from your query
    *
    * @param string $sql
    * @return array $result
    **/
    function queryArray($sql) {
        $result = $this->query($sql);
        return $result;
    }

    /**
    * queryRow will return just the first row from your query, and saves
    * you the step of breaking into the multi-dimesional array to find the result.
    *
    * @param string $sql
    * @return array $result
    **/
    function queryRow($sql) {
        $result = $this->query($sql);
        return $result[0];
    }

    /**
    * queryField will return just a single value.  The first value from the 
    * first row. This helps alot when you just need to get an id or something simple.
    *
    * @param string $sql
    * @return string $value
    **/
    function queryField($sql) {
        $result = $this->query($sql);
        foreach ($result[0] as $value) {
            return $value;
        }
    }

    /**
    * Singleton control. If object with specified name does not exist, it will be created and returned.
    * If it does exist it will be returned as well.
    *
    * @param string $instanceName Name of object to return.
    */
    public function getInstance($instanceName="default", $configName=null) {
        if (!isset(self::$instance[$instanceName])) {
            $object= __CLASS__;
            self::$instance[$instanceName] = new $object($instanceName, $configName);
        }
        return self::$instance[$instanceName];
    }
}

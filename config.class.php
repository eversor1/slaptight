<?
class config {
    public static $instance;
    public $instanceName;
    private $configFile="config.ini";
    public $config;
    

    function __construct($instanceName="default", $configFile=null) {
        //if an alternate config file is specified, lets use that
        if (isset($configFile)) {
            $this->configFile = $configFile;
        }
        $this->config = parse_ini_file($this->configFile, false) or die("Error Opening confguration file: ".$this->configFile);
    }

    /**
    * Retrieve the value of the named property from the config file.
    *
    * @param string $propertyName
    * @return string $propertyValue
    */
    function get($property) {
        return $this->config[$property];
    }

    /**
    * Set a specific property in the config record to a value
    *
    * @param string $property
    * @param string $value
    */
    function set($property, $value) {
        $this->config[$property] = $value;
    }
    
    /**
    * Singleton vendor for the config class.
    *
    * @return object $self
    */
    function getInstance($instanceName=null, $configFile=null) {
        if (!isset($instanceName)) {
            $instanceName = "default";
        }
        if (!isset(self::$instance[$instanceName])) {
            $object = __CLASS__;
            self::$instance[$instanceName] = new $object($instanceName, $configFile);
        }
        return self::$instance[$instanceName];
    }
}

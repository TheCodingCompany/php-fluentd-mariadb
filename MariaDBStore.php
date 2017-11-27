<?php
/**
 * Intellectual Property of Svensk Coding Company AB - Sweden All rights reserved.
 * 
 * @copyright (c) 2016, Svensk Coding Company AB
 * @author V.A. (Victor) Angelier <victor@thecodingcompany.se>
 * @version 1.0
 * @license http://www.apache.org/licenses/GPL-compatibility.html GPL
 * 
 */
namespace theCodingCompany;

use PDO;

/**
 * Class to route FluentD output to MariaDB store
 */
class MariaDBStore
{
    /**
     * MariaDB PDO connection
     * @var type
     */
    protected $db_con = null;

    /**
     * Settings as key value array ["cdn" => "", "username" => "", "password" => ""]
     * @var array 
     */
    private $settings = [];

    /**
     * The name of the database tabel where to store the data
     * @var string
     */
    private $db_table = null;

    /**
     * The fieldnames we use for the insert
     * @var array
     */
    private $db_fields = [];

    /**
     * The values to insert
     * @var array
     */
    private $db_values = [];

    /**
     * Construct new Class
     */
    public function __construct($settings = [])
    {
        $this->settings = $settings;
        $this->DBConnect();
    }

    /**
     * Start the reader
     */
    public function start()
    {
        $this->log(date("Ymd H:i:s"). " MariaDB Storage running......");
        while(true)
        {
            $line = trim(fgets(STDIN));
            if($line !== ""){

                $this->log($line);
                $this->getValues($line)
                    ->insert();
            }
        }
        $this->log(date("Ymd H:i:s"). " MariaDB Storage stopped");
    }

    /**
     * Destruct
     */
    public function __destruct()
    {
        $this->log(date("Ymd H:i:s"). " MariaDB Storage stopped");
    }

    /**
     * Get the values to insert
     * @param string $json
     * @return void
     */
    private function getValues($json = null)
    {
        $this->db_values = []; //Reset to nothing
        
        if(($assoc = json_decode($json, true)) !== NULL){
            
            foreach($this->db_fields as $field){
                if(isset($assoc[$field])){
                    array_push($this->db_values, json_encode($assoc[$field]));
                }
            }
            
        }else{
            $this->log("Cant decode JSON:\r\n " . print_r($json, true) . "\r\n");
        }
        return $this;
    }

    /**
     * Connect to the MariaDB database
     */
    private function DBConnect()
    {
        /**
         * Connect to the database
         */
        try
        {
            $this->db_con = new PDO(
                $this->settings["cdn"],
                $this->settings["username"],
                $this->settings["password"], [
                    PDO::MYSQL_ATTR_INIT_COMMAND    => "SET NAMES 'UTF8'",
                    PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_AUTOCOMMIT            => TRUE
                ]
            );
            
        }
        catch(\Exception $err)
        {
            $this->log($err->getMessage());
            $this->log(print_r($err->getTraceAsString(), true));
        }
    }

    /**
     * Set the table for our insert statement
     * @param string $tablename
     * @return \Fluentd\MariaDBStore
     */
    public function setTable($tablename = "")
    {
        $this->db_table = $tablename;
        
        return $this;
    }

    /**
     * Set the table fieldnames
     * @param array $fields
     * @return \Fluentd\MariaDBStore
     */
    public function setFields($fields = [])
    {
        $this->db_fields = $fields;

        return $this;
    }

    /**
     * Insert data into the table
     * @return boolean
     */
    public function insert()
    {
        $sql = $this->createInsertSQL();

        try
        {
            $stmt = $this->db_con->prepare($sql);
            if($stmt->execute() === FALSE){
                $this->log(print_r($this->db_con->errorInfo(), true));
            }
        }
        catch(\Exception $err)
        {
            $this->log($err->getMessage());
            $this->log(print_r($err->getTraceAsString(), true));
        }

        return true;
    }

    /**
     * Create the SQL insert statement
     * @todo Needs rework. Didn't find it important enough yet. Feel free to do so.
     * @return string
     */
    private function createInsertSQL()
    {
        $field_data = "";
        foreach($this->db_fields as $field){
            $field_data .= "`{$field}`,";
        }
        $field_data = substr($field_data, 0, -1);

        $value_data = "";
        foreach($this->db_values as $val){
            $value_data .= "{$this->db_con->quote($val)},";
        }
        $value_data = substr($value_data, 0, -1);

        return "INSERT INTO `{$this->db_table}` ({$field_data}) VALUES({$value_data})";
    }

    /**
     * Log info to file
     * @param type $string
     */
    private function log($string = "")
    {
        syslog(LOG_INFO, $string);

        file_put_contents("/var/log/fluentd_log", $string."\r\n", FILE_APPEND);
    }
}
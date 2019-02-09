<?php
    class myHeartdb{
        //Properties
        private $dbhost = "localhost";
        private $dbuser = "root";
        private $dbpassword = "";
        private $dbname = "myHeart";

        public $connection = null;

        //Connect
        public function __construct(){
            $this->connection = new mysqli($this->dbhost, $this->dbuser, $this->dbpassword, $this->dbname) or die("Could not connect to the database");
        }

        public function get_connection(){
            return $this->connection;
        }

    }

?>
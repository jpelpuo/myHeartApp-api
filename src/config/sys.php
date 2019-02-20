<?php
    class userControl{
        private $db = null;
        private$connection = null;
        private $does_exist = false;

        
        public function __construct(){
            $this->db = new myHeartdb();
            $this->connection = $this->db->get_connection();
        }

        public function does_user_exist($email){
            $sql = "SELECT * FROM users WHERE email = '$email'";

            $rows = $this->connection->query($sql);
            $count = $rows->num_rows;

            if($count > 0){
                $this->does_exist = true;
            }else{
                $this->does_exist = false;
            }

            return $this->does_exist;
        }

        public function getUsers(){
            $sql = "SELECT * FROM users";

            $rs = $this->connection->query($sql);
            $rows = $rs->fetch_all(MYSQLI_ASSOC);
            return $rows;
        }

        public function getHealthInfo($email){
            $sql = "SELECT * FROM userhealth WHERE email = '$email'";
        }
    }


    class model{
        // Attributes
        private $flour = null;
        private $sugar = null;
        public $prediction_result = null;

        function __construct($flour, $sugar){
            $this->flour = $flour;
            $this->sugar = $sugar;
        }

        function getPrediction(){
            $attributes = array($this->flour, $this->sugar);
            $result = shell_exec('python "C:/xampp/htdocs/myHeart-model/model.py" ' .escapeshellarg(json_encode($attributes)));
            if(!(is_null($result))){
                $this->prediction_result = $result;
            }else{
                $this->prediction_result = null;
            }
            return $this->prediction_result;
        }
        
    }

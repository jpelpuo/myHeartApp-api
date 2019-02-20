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
        private $age = null;
        private $sex = null;
        private $chest_pain = null;
        private $blood_pressure = null;
        private $serum_cholestoral = null;
        private $fasting_blood_sugar = null;
        private $resting_ECG = null;
        private $max_heart_rate = null;
        private $induced_angina = null;
        private $ST_depression = null;
        private $slope = null;
        private $no_of_vessels = null;
        private $thal = null;
        private $diagnosis = null;
        public $prediction_result = null;

        function __construct($age, $sex, $chest_pain, $blood_pressure, $serum_cholestoral, $fasting_blood_sugar, $resting_ECG, $max_heart_rate, $induced_angina, $ST_depression, $slope, $no_of_vessels, $thal){
            $this->age = $age;
            $this->sex = $sex;
            $this->chest_pain = $chest_pain; 
            $this->blood_pressure = $blood_pressure;
            $this->serum_cholestoral = $serum_cholestoral;
            $this->fasting_blood_sugar = $fasting_blood_sugar;
            $this->resting_ECG = $resting_ECG;
            $this->max_heart_rate = $max_heart_rate;
            $this->induced_angina = $induced_angina;
            $this->ST_depression = $ST_depression;
            $this->slope = $slope;
            $this->no_of_vessels = $no_of_vessels;
            $this->thal = $thal;
        }

        function getPrediction(){
            $attributes = array(
                $this->age, 
                $this->sex,
                $this->chest_pain, 
                $this->blood_pressure,
                $this->serum_cholestoral, 
                $this->fasting_blood_sugar,
                $this->resting_ECG, 
                $this->max_heart_rate,
                $this->induced_angina, 
                $this->ST_depression,
                $this->slope, 
                $this->no_of_vessels,
                $this->thal
            );
            $result = shell_exec('python "C:/xampp/htdocs/myHeart-model/predictive-model.py" ' .escapeshellarg(json_encode($attributes)));
            
            if(!(is_null($result))){
                $this->prediction_result = $result;
            }else{
                $this->prediction_result = null;
            }
            return $this->prediction_result;
        }
        
    }

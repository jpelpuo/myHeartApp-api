<?php

    require 'PHPMailerAutoload.php';

    class userControl{
        private $db = null;
        private $connection = null;
        private $does_exist = false;

        
        public function __construct(){
            $this->db = new myHeartdb();
            $this->connection = $this->db->get_connection();
        }

        //Check if patient ID exists
        public function does_id_exist($patient_id){
            $sql = "SELECT * FROM patient WHERE patient_id = '$patient_id'";

            $rows = $this->connection->query($sql);
            $count = $rows->num_rows;

            if($count > 0){
                $this->does_exist = true;
            }else{
                $this->does_exist = false;
            }

            return $this->does_exist;
        }

        public function getPatients(){
            $sql = "SELECT * FROM patient";

            $rs = $this->connection->query($sql);
            $count = $rs->num_rows;
            return $count;
        }

        // public function getsex($patient_id)

        public function setPatientId(){
            //$no_of_users = $this->getUsers();
            $sql = "SELECT * FROM patient ORDER BY patient_id DESC LIMIT 1";

            $rs = $this->connection->query($sql);
            $row = $rs->fetch_all(MYSQLI_ASSOC);
            $last_id = $row[0]['patient_id'];
            $last_count = (int)substr($last_id, 2);
            $counter = $last_count + 1;
            if($counter < 10){
                $patient_id = "LH000".$counter;
            }else if($counter >= 10 && $counter  < 100){
                $patient_id = "LH00".$counter;
            }else if($counter >=100 && $counter < 1000){
                $patient_id = "LH0".$counter;
            }else{
                $patient_id = "LH".$counter;
            }
            
            return $patient_id;
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
            $result = shell_exec('python "C:/xampp/htdocs/myHeart-model/diagnose.py" ' .escapeshellarg(json_encode($attributes)));
            
            if(!(is_null($result))){
                $this->prediction_result = $result;
            }else{
                $this->prediction_result = null;
            }
            return $this->prediction_result;
        }
        
    }

    class Mail{
        private $name = null;
        private $email = null;
        private $patient_id = null;
        private $password = null;

        public function __construct($email, $patient_id, $password, $name){
            $this->name = $name;
            $this->email = $email;
            $this->patient_id = $patient_id;
            $this->password = $password;
        }

        public function sendMail(){
            $mail = new PHPMailer;

            //$mail->SMTPDebug = 3;                               // Enable verbose debug output

            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'myheartdiseaseapp@gmail.com';                 // SMTP username
            $mail->Password = 'myheart1*';                           // SMTP password
            $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;                                    // TCP port to connect to

            $mail->setFrom('myheartdiseaseapp@gmail.com', 'myHeart');
            $mail->addAddress($this->email);     // Add a recipient
            //$mail->addAddress('ellen@example.com');               // Name is optional
            $mail->addReplyTo('myheartdiseaseapp@gmail.com');
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');

            // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
            $mail->isHTML(true);                                  // Set email format to HTML

            $mail->Subject = 'myHeart Registration Details';
            $mail->Body    = "<div style='font-size:16px;'>Hello ".$this->name.","."<p>Find in this email your login details for myHeart app.</p> <p><strong>Patient ID</strong>: ".$this->patient_id."</p>"."<p><strong>Password</strong>: ".$this->password."</p></div>";
            $mail->AltBody = "Hello, \r\n".$this->name."Your login details are: \r\n Patient ID: ".$this->patient_id."\r\n"."Password: ".$this->password;

            // if(!$mail->send()) {
            //     echo 'Message could not be sent.';
            //     echo 'Mailer Error: ' . $mail->ErrorInfo;
            // } else {
            //     echo 'Message has been sent';
            // }

            $mail->send();
            }
            
    }

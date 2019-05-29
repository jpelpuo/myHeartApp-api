<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App;

$app->post('/api/login', function(Request $request, Response $response){
    $patient_id = $request->getParam('patient_id');
    $password = $request->getParam('password');
    $stmnt = null;

    $sql = "SELECT * from patient where patient_id = '$patient_id'";

    try{
         $db = new myHeartdb();
         $con = $db->get_connection();
         $stmt = $con->query($sql);

         $patient = $stmt->fetch_all(MYSQLI_ASSOC);

        if(password_verify($password, $patient[0]['password'])){
            $json_success = array(
                "Response Code" => $response->getStatusCode(),
                "Response Message" => "Success",
                "Success" => true,
                "Data" => $patient
            );
            $response->withJson($json_success);
        }else{
            $json_failure = array(
                "Response Code" => $response->getStatusCode(),
                "Response Message" => "User not found",
                "Success" => false
            );
            $response->withJson($json_failure);
         }

         
    }catch(mysqli_sqli_exception $e){
        $response->getBody()->write($e->errorMessage());
    }

    return $response->withHeader('content-type', 'application/json');
});


$app->post('/api/add', function(Request $request, Response $response){
    $user = new userControl();

    $firstname = $request->getParam('firstname');
    $lastname = $request->getParam('lastname');
    $name = $firstname ." ".$lastname;
    $email = $request->getParam('email');
    $sex = $request->getParam('sex');
    $dob = $request->getParam('dob');
    $contact = $request->getParam('contact');
    $patient_id = $user->setPatientId();
    $password = rand(1000, 10000);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO patient (patient_id, name,email, sex, dob, password) values('$patient_id', '$name', '$email','$sex', '$dob', '$hashed_password')";

    try{
         $db = new myHeartdb();
         $con = $db->get_connection();
         $mailer = new Mail($email, $patient_id, $password, $firstname);
         
         //$validate = new userControl();

         $json_success = array(
                "Response Code" => $response->getStatusCode(),
                "Response Message" => "Registration successful",
                "Success" => true
             );

        $json_failure = array(
                "Response Code" => $response->getStatusCode(),
                "Response Message" => "User already exists",
                "Success" => false
             );
        
        $stmt = $con->query($sql);
        if($stmt){
            $response->withJson($json_success);
            //Send mail to user
            // $to = $email;
            // $subject = "myHeart Registration Details";
            // $message = "Hello ".$name. ",\r\n"."Your login details are: \r\n Patient ID: ".$patient_id."\r\n"."Password: ".$password;
            // mail($to, $subject, $message);
            $mailer->sendMail();
        }else{
            $response->withJson($json_failure);
        }
        //}
         

    }catch(mysqli_sqli_exception $e){
        echo "Error:" .$e->errorMessage();
    }

     return $response->withHeader('content-type', 'application/json');
});


$app->post('/api/predict', function(Request $request, Response $response){
    //Get attributes from the API request
    // $email = $request->getParam('email')
    $age = $request->getParam('age');
    $sex = $request->getParam('sex');
    $chest_pain = $request->getParam('chest_pain');
    $blood_pressure = $request->getParam('blood_pressure');
    $serum_cholestoral = $request->getParam('serum_cholestoral');
    $fasting_blood_sugar = $request->getParam('fasting_blood_sugar');
    $resting_ECG = $request->getParam('resting_ECG');
    $max_heart_rate = $request->getParam('max_heart_rate');
    $induced_angina = $request->getParam('induced_angina');
    $ST_depression = $request->getParam('ST_depression');
    $slope = $request->getParam('slope');
    $no_of_vessels = $request->getParam('no_of_vessels');
    $thal = $request->getParam('thal');
    $diagnosis = null;
    $prediction_result = null;

    //Instantiate model with the prediction attriutes
    $model = new model($age, $sex, $chest_pain, $blood_pressure, $serum_cholestoral, $fasting_blood_sugar, $resting_ECG, $max_heart_rate, $induced_angina, $ST_depression, $slope, $no_of_vessels, $thal);

    $diagnosis = $model->getPrediction();

    $diagnosis_data = json_decode($diagnosis, true);

    $json_success = array(
        "Response Code" => $response->getStatusCode(),
        "Response Message" => "Prediction Successful",
        "Success" => true,
        "data" => $diagnosis_data
    );

    $json_failure = array(
        "Response Code" => $response->getStatusCode(),
        "Response Message" => "Prediction Unsuccessful",
        "Success" => false,
        "data" => $diagnosis_data
    );

    if(!($diagnosis == null)){
        $response->withJson($json_success);
    }else{
        $response->withJson($json_failure);
    }

    return $response->withHeader('content-type', 'application/json');

});


$app->post('/api/save', function(Request $request, Response $response){
    $patient_id = $request->getParam('patient_id');
    $age = $request->getParam('age');
    $sex = $request->getParam('sex');
    $chest_pain = $request->getParam('chest_pain');
    $blood_pressure = $request->getParam('blood_pressure');
    $serum_cholestoral = $request->getParam('serum_cholestoral');
    $fasting_blood_sugar = $request->getParam('fasting_blood_sugar');
    $resting_ECG = $request->getParam('resting_ECG');
    $max_heart_rate = $request->getParam('max_heart_rate');
    $induced_angina = $request->getParam('induced_angina');
    $ST_depression = $request->getParam('ST_depression');
    $slope = $request->getParam('slope');
    $no_of_vessels = $request->getParam('no_of_vessels');
    $thal = $request->getParam('thal');
    $diagnosis = $request->getParam('diagnosis');
    $diagnosis_percent = $request->getParam('diagnosis_percent');
    $prediction_date = date("Y-m-d");

    $sql = "INSERT INTO prediction (patient_id, age, sex, chest_pain, resting_blood_pressure, serum_cholesterol, fasting_blood_sugar, resting_ecg, max_heart_rate, induced_angina, st_depression, slope, no_of_vessels, thal, diagnosis, diagnosis_percent, prediction_date) values('$patient_id', '$age', '$sex', '$chest_pain', '$blood_pressure', '$serum_cholestoral', '$fasting_blood_sugar', '$resting_ECG', '$max_heart_rate', '$induced_angina', '$ST_depression', '$slope', '$no_of_vessels', '$thal', '$diagnosis', '$diagnosis_percent', '$prediction_date')";

    try{
         $db = new myHeartdb();
         $con = $db->get_connection();
         //$validate = new userControl();

        $json_success = array(
                "Response Code" => $response->getStatusCode(),
                "Response Message" => "Details saved",
                "Success" => true
             );

        $json_failure = array(
                "Response Code" => $response->getStatusCode(),
                "Response Message" => "Save unsuccessful. User not registered.",
                "Success" => false
             );

        $stmt = $con->query($sql);
        if($stmt){
            $response->withJson($json_success);
        }else{
            $response->withJson($json_failure);
        }
        //}
         

    }catch(mysqli_sqli_exception $e){
        echo "Error:" .$e->errorMessage();
    }

    return $response->withHeader('content-type', 'application/json');
});


$app->get('/api/prediction/{patient_id}', function(Request $request, Response $response){
    $patient_id = $request->getAttribute('patient_id');

    $sql = "SELECT * from prediction where patient_id = '$patient_id'";

    try{
        $db = new myHeartdb();
        $con = $db->get_connection();

        $stmt = $con->query($sql);

        $prediction = $stmt->fetch_all(MYSQLI_ASSOC);  

        $json_success = array(
            "Response Code" => $response->getStatusCode(),
            "Response Message" => "Success",
            "Success" => true,
            "Data" => $prediction
        );

        $json_failure = array(
            "Response Code" => $response->getStatusCode(),
            "Response Message" => "No predictions found",
            "Success" => false
        );

        if($stmt->num_rows > 0){
            $response->withJson($json_success); 
        }else{
            $response->withJson($json_failure);
        }

    }catch(mysqli_sqli_exception $e){
        $response->getBody()->write($e->errorMessage());
    }

    return $response->withHeader('content-type', 'application/json');
});

$app->get('/api/get/{patient_id}', function(Request $request, Response $response){
    $patient_id = $request->getAttribute('patient_id');

    $sql = "SELECT * from prediction right join patient on prediction.patient_id = patient.patient_id where prediction.patient_id = '$patient_id'";

    $sql_query = "SELECT * from patient where patient_id = '$patient_id'";

    try{
        $db = new myHeartdb();
        $con = $db->get_connection();

        $stmt = $con->query($sql);

        $stmt_query = $con->query($sql_query);

        $prediction = $stmt->fetch_all(MYSQLI_ASSOC); 
        
        $details = $stmt_query->fetch_assoc();

        $no_prediction = array(
            "diagnosis_percent" => 0,
            "prediction_date" => "No prediction history found"
        );

        $all_details = array_merge($details, $no_prediction);

        $json_success = array(
            "Response Code" => $response->getStatusCode(),
            "Response Message" => "Success",
            "Success" => true,
            "Data" => $prediction
        );

        $json_failure = array(
            "Response Code" => $response->getStatusCode(),
            "Response Message" => "No predictions found",
            "Success" => false,
            "Data" => array($all_details)
        );

        if($stmt->num_rows > 0){
            $response->withJson($json_success); 
        }else{
            $response->withJson($json_failure);
        }

    }catch(mysqli_sqli_exception $e){
        $response->getBody()->write($e->errorMessage());
    }

    return $response->withHeader('content-type', 'application/json');
});
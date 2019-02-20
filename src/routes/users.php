<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App;

$app->post('/api/login', function(Request $request, Response $response){
    $email = $request->getParam('email');
    $password = $request->getParam('password');
    $stmnt = null;

    $sql = "SELECT * from users where email = '$email'";

    try{
         $db = new myHeartdb();
         $con = $db->get_connection();
         $stmt = $con->query($sql);

         $user = $stmt->fetch_all(MYSQLI_ASSOC);

        if(password_verify($password, $user[0]['password'])){
            $json_success = array(
                "responseCode" => $response->getStatusCode(),
                "responseMessage" => "Success",
                "success" => true,
                "data" => $user
            );
            $response->withJson($json_success);
        }else{
            $json_failure = array(
                "responseCode" => $response->getStatusCode(),
                "responseMessage" => "User not found",
                "success" => false
            );
            $response->withJson($json_failure);
         }

         
    }catch(mysqli_sqli_exception $e){
        echo "Error:" .$e->errorMessage();
    }

    return $response->withHeader('content-type', 'application/json');
});


$app->post('/api/register', function(Request $request, Response $response){
    $name = $request->getParam('name');
    $email = $request->getParam('email');
    $password = $request->getParam('password');
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, password) values('$name', '$email', '$hashed_password')";

    try{
         $db = new myHeartdb();
         $con = $db->get_connection();
         $validate = new userControl();

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
        
        // //if($validate->does_user_exist($email)){
        //     $json_exists = array(
        //         "responseCode" => $response->getStatusCode(),
        //         "responseMessage" => "User already exists",
        //         "success" => false
        //      );
        //      $response->withJson($json_exists);
        // }else{
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


$app->post('/api/predict', function(Request $request, Response $response){
    $flour = $request->getParam('flour');
    $sugar = $request->getParam('sugar');

    $model = new model($flour,$sugar);
    $prediction = $model->getPrediction();

    $prediction_data = json_decode($prediction, true);

    $json_success = array(
        "Response Code" => $response->getStatusCode(),
        "Response Message" => "Prediction Successful",
        "Success" => true,
        "data" => $prediction_data
    );

    $json_failure = array(
        "Response Code" => $response->getStatusCode(),
        "Response Message" => "Prediction Unsuccessful",
        "Success" => false,
        "data" => $prediction
    );

    if(!($prediction == null)){
        $response->withJson($json_success);
    }else{
        $response->withJson($json_failure);
    }

    return $response->withHeader('content-type', 'application/json');

});


$app->put('/api/update', function(Request $request, Response $response){
    //$name = $request->getParam('name');
    $email = $request->getParam('email');
    $password = $request->getParam('password');
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "UPDATE users SET password = '$hashed_password' WHERE email = '$email'";

     try{
         $db = new myHeartdb();
         $con = $db->get_connection();
         $validate = new userControl();

         $json_success = array(
                "responseCode" => $response->getStatusCode(),
                "responseMessage" => "Update successful",
                "success" => true
             );

        $json_failure = array(
                "responseCode" => $response->getStatusCode(),
                "responseMessage" => "Update unsuccessful",
                "success" => false
             );
        
            $stmt = $con->query($sql);

            if($stmt){
                $response->withJson($json_success);
            }else{
                $response->withJson($json_failure);
            }
         

    }catch(mysqli_sqli_exception $e){
        echo "Error:" .$e->errorMessage();
    }

     return $response->withHeader('content-type', 'application/json');

});
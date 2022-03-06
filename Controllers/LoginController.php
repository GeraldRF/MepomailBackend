<?php

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: X-Requested-With");

include_once "../BS/MailServices.php";

$Services = new UserServices();

// LOGUEARSE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $response = $Services->checkUserLogin($_POST["email"], $_POST["password"]);

    if ($response["isVerified"]) {

        header("HTTP/1.1 200 OK");
        echo json_encode($response["user"]);

    } else {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode($response["msg"]);
    }

    exit();
}


//En caso de que ninguna de las opciones anteriores se haya ejecutado
header("HTTP/1.1 400 Bad Request");
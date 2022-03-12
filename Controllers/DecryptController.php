<?php

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: X-Requested-With, X-Custom-Header, Authorization");

//Revisar autheticacion
$headers = getallheaders();
if (isset($headers["Authorization"])) {
    $token = $headers["Authorization"];
    if (explode(" ", $token)[1] != "$2y$10\$GwGaUqJK3uPib0iaxS6B9u2uLXh6z4Fok3hGec1/wwfAmFNazXB7.") {
        header("HTTP/1.1 401 Unauthorized");
        echo "<h1 style=\"width:95%; text-align:center; color:red; padding:40px; background-color:#ff03033b;\">Acceso no autorizado</h1>";
        exit();
    }
} else {
    header("HTTP/1.1 401 Unauthorized");
    echo "<h1 style=\"width:95%; text-align:center; color:red; padding:40px; background-color:#ff03033b;\">Acceso no autorizado</h1>";
    exit();
}


include_once "../BS/MailServices.php";

$Services = new MailServices();

//Desencriptar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_GET['revisar'])) {


    $input = $_POST;

    $response = $Services->decryptMail($_GET['id'], $input['clave']);

    $mail = $Services->findMail($_GET['id']);

    if ($mail['has_files'] == 1 && $response['isDescrypted']) {
        $fileData = $Services->getFile($mail['id']);
       // $file = fopen('../' . $fileData['uri'], 'r');
       $data = ['body' =>$response["body"], 'file_id' => $fileData['id']];
    }else if($response['isDescrypted']){
        $data = ['body' =>$response["body"]];
    }

    if ($response["isDescrypted"]) {
        header("HTTP/1.1 200 OK");
        $Services->updateMail($_GET['id'], ['readed' => 1]);

        echo json_encode($data);
    } else {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode(['body'=>$response["msg"]]);
    }

    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['revisar'])) {

    $input = $_POST;

    $response = $Services->decryptMail($_GET['id'], $input['clave']);

    $mail = $Services->findMail($_GET['id']);

    if ($mail['has_files'] == 1 && $response['isDescrypted']) {
        $fileData = $Services->getFile($mail['id']);
       // $file = fopen('../' . $fileData['uri'], 'r');
       $data = ['body' =>$response["body"], 'file_id' => $fileData['id']];
    }else if($response['isDescrypted']){
        $data = ['body' =>$response["body"]];
    }

    if ($response["isDescrypted"]) {
        header("HTTP/1.1 200 OK");
        echo json_encode($data);
    } else {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode(['body'=>$response["msg"]]);
    }

    exit();
}


//En caso de que ninguna de las opciones anteriores se haya ejecutado
header("HTTP/1.1 400 Bad Request");

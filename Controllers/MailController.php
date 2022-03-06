<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: X-Requested-With");

include_once "../BS/MailServices.php";

$Services = new MailServices();

/*
  listar todos los Mail o solo uno
 */
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    include ("../BS/Auto_eliminador.php");

    if (isset($_GET['id'])) {

        //Mostrar un usuario
        header("HTTP/1.1 200 OK");
        echo json_encode($Services->findMail($_GET['id']));

        exit();
    } else {

        //Mostrar todos los usuarios
        header("HTTP/1.1 200 OK");
        echo json_encode($Services->getAllMails());

        exit();
    }
}

// Crear un nuevo Mail
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_GET['id'])) {

    $input = $_POST;

    $response = $Services->createMail($input);

    if ($response["isCreated"]) {

        header("HTTP/1.1 200 OK");
        echo json_encode("Mensaje enviado correctamente.");
    } else {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode($response["msg"]);
    }

    exit();
}


//Desactivar | Borrar
if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {

    $isDeleted = $Services->DeleteMail($_GET['id']);

    if ($isDeleted) {
        header("HTTP/1.1 200 OK");
    } else {
        header("HTTP/1.1 400 OK");
    }

    exit();
}


//Actualizar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['id'])) {

    $input = $_POST;

    $response = $Services->updateMail($_GET['id'], $input);

    if ($response["isUpdated"]) {
        header("HTTP/1.1 200 OK");
        echo json_encode($input);
    } else {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode($response["msg"]);   
    }

    exit();
}


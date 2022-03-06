<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: X-Requested-With");

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

/*
  listar todos los Mail o solo uno
 */
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    include("../BS/Auto_eliminador.php");

    if (isset($_GET['id'])) {

        //Mostrar un usuario
        header("HTTP/1.1 200 OK");
        echo json_encode($Services->findMail($_GET['id']));

        exit();
    } else if (!isset($_GET['email']) && !isset($_GET['solicitud'])) {

        //Mostrar todos los usuarios
        header("HTTP/1.1 200 OK");
        echo json_encode($Services->getAllMails());

        exit();
    } else {
        if ($_GET['solicitud'] === "recibidos") {

            //Mostrar todos los usuarios
            header("HTTP/1.1 200 OK");
            echo json_encode($Services->getReceivedMails($_GET['email']));
        } else {
            //Mostrar todos los usuarios
            header("HTTP/1.1 200 OK");
            echo json_encode($Services->getSentMails($_GET['email']));
        }
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

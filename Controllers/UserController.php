<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: X-Requested-With");

include_once "../BS/UserServices.php";

$Services = new UserServices();

/*
  listar todos los user o solo uno
 */
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    if (isset($_GET['email'])) {

        //Mostrar un usuario
        header("HTTP/1.1 200 OK");
        echo json_encode($Services->findUser($_GET['email']));

        exit();
    } else {

        //Mostrar todos los usuarios
        header("HTTP/1.1 200 OK");
        echo json_encode($Services->getAllUsers());

        exit();
    }
}

// Crear un nuevo user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_GET['email']) && !isset($_GET['Login'])) {

    $input = $_POST;

    $response = $Services->createUser($input);

    if ($response["isCreated"]) {

        header("HTTP/1.1 200 OK");
        echo json_encode($input);
    } else {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode($response["msg"]);
    }

    exit();
}


//Desactivar | Borrar
if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {

    $isDesactivated = $Services->desactiveUser($_GET['email']);

    if ($isDesactivated) {
        header("HTTP/1.1 200 OK");
    } else {
        header("HTTP/1.1 400 OK");
    }

    exit();
}


//Actualizar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['email']) && !isset($_GET['Login'])) {

    $input = $_POST;

    $response = $Services->updateUser($_GET['email'], $input);

    if ($response["isUpdated"]) {
        header("HTTP/1.1 200 OK");
        echo json_encode($input);
    } else {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode($response["msg"]);   
    }

    exit();
}

// LOGUEARSE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['Login'])) {

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

<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: X-Requested-With, X-Custom-Header, Authorization");

//Revisar autheticacion
$headers = getallheaders();
if(isset($headers["Authorization"])){
$token = $headers["Authorization"];
if(explode(" ", $token)[1] != "$2y$10\$GwGaUqJK3uPib0iaxS6B9u2uLXh6z4Fok3hGec1/wwfAmFNazXB7.")
{
    header("HTTP/1.1 401 Unauthorized");
    echo "<h1 style=\"width:95%; text-align:center; color:red; padding:40px; background-color:#ff03033b;\">Acceso no autorizado</h1>";
    exit();
}
}else{
    header("HTTP/1.1 401 Unauthorized");
    echo "<h1 style=\"width:95%; text-align:center; color:red; padding:40px; background-color:#ff03033b;\">Acceso no autorizado</h1>";
    exit();
}


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
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_GET['email'])) {

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
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['email'])) {

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


//En caso de que ninguna de las opciones anteriores se haya ejecutado
header("HTTP/1.1 400 Bad Request");

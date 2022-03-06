<?php

//Metodo de encriptación
$method = 'aes-256-cbc';

//Encripta el mensaje
$encriptar = function ($msg, $clave, $iv) use ($method) {
    return openssl_encrypt($msg, $method, $clave, false, $iv);
};

//Desencripta el mensaje
$desencriptar = function ($msg, $clave, $iv) use ($method) {
    return openssl_decrypt($msg, $method, $clave, false, $iv);
};

//Genera un IV
$getIV = function () use ($method) {
    return base64_encode(openssl_random_pseudo_bytes(openssl_cipher_iv_length($method)));
};

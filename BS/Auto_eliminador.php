<?php

include_once "../BS/MailServices.php";

$Services = new MailServices();

$mails = $Services->getAllMails();

$date = new DateTime();

foreach ($mails as $mail) {

    $delete_date = new DateTime($mail['delete_date']);

    if ($delete_date <= $date) {
        echo "El correo con id " . $mail['id'] . " sera eliminado por fecha <br>";
        $Services->DeleteMail($mail['id']);
    }

}

<?php

class MailServices
{
    public $dbConn;

    function __construct()
    {
        include "../DA/DBConfig.php";
        include "../DA/QueryUtils.php";


        $this->dbConn =  connect(
            array(
                'host' => HOST,
                'username' => USERNAME,
                'password' => PASSWORD,
                'db' => DB
            )
        );
    }

    function getAllMails()
    {
        $sql = $this->dbConn->prepare("SELECT * FROM mails");
        $sql->execute();

        $sql->setFetchMode(PDO::FETCH_ASSOC);

        return  $sql->fetchAll();
    }

    function findMail($id)
    {
        $sql = $this->dbConn->prepare("SELECT * FROM mails WHERE id=:id");
        $sql->bindValue(':id', $id);
        $sql->execute();

        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    function getReceivedMails($email)
    {
        $sql = $this->dbConn->prepare("SELECT * FROM mails WHERE receiver=:email");
        $sql->bindValue(":email", $email);
        $sql->execute();

        $sql->setFetchMode(PDO::FETCH_ASSOC);

        return  $sql->fetchAll();
    }

    function getSentMails($email)
    {
        $sql = $this->dbConn->prepare("SELECT * FROM mails WHERE transmitter=:email");
        $sql->bindValue(":email", $email);
        $sql->execute();

        $sql->setFetchMode(PDO::FETCH_ASSOC);

        return  $sql->fetchAll();
    }



    function deleteMail($id)
    {
        try {
            $statement = $this->dbConn->prepare("DELETE FROM mails WHERE id=:id");
            $statement->bindValue(':id', $id);
            $statement->execute();

            return true;
        } catch (PDOException $exception) {
            return false;
        }
    }

    function createMail($input)
    {
        include "Encriptador.php";

        $acepted_id = false;
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        $id_generated = '';
        do {

            $id_generated = substr(str_shuffle($permitted_chars), 0, 16);

            $result = $this->findMail($id_generated);

            if (!isset($result['id'])) {
                $acepted_id = true;
            }
        } while (!$acepted_id);

        $iv = substr($getIV(), 0, 16);
        $body = $encriptar($input['body'], $input['clave'], $iv);

        $sql = "INSERT INTO mails 
                (id, iv, transmitter, receiver, subject, body, has_files, delete_date)
                VALUES
                (:id, :iv, :transmitter, :receiver, :subject, :body, :has_files, :delete_date)";

        try {

            $statement = $this->dbConn->prepare($sql);

            if (!empty($_FILES['file'])) {
                $has_file = 1;
            } else {
                $has_file = 0;
            }

            if ($has_file === 1) {
                $file_uploaded = $this->setFile($id_generated);
                if (!$file_uploaded['isUploaded']) {
                    throw new PDOException('Fallo al subir archivo');
                };
            }

            $statement->execute(array(
                ':id' => $id_generated, ':iv' => $iv, ':transmitter' => $input['transmitter'], ':receiver' => $input['receiver'],
                ':subject' => $input['subject'], ':body' => $body, ':has_files' => $has_file, ':delete_date' => $input['delete_date']
            ));


            $id =  $id_generated.$_FILES['file']['name'];

            $sql = "INSERT INTO files 
                (id, mail_id, uri)
                VALUES
                (:id, :mail_id, :uri)";


            $statement = $this->dbConn->prepare($sql);

            $statement->execute(array(
                ':id' => $id, ':mail_id' => $id_generated, ':uri' => 'Uploads/'.$id
            ));

            return ["isCreated" => true];
        } catch (PDOException $exception) {
            unlink($file_uploaded['ruta']);
            return ["isCreated" => false, "msg" => $exception->getMessage()];
        }
    }

    function updateMail($id, $input)
    {

        $fields = getParams($input);

        $sql = "UPDATE mails
          SET $fields
          WHERE id='$id'";

        try {

            $statement = $this->dbConn->prepare($sql);
            bindAllValues($statement, $input);

            $statement->execute();

            return ["isUpdated" => true];
        } catch (PDOException $exception) {

            return ["isUpdated" => false, "msg" => $exception->getMessage()];
        }
    }

    function DecryptMail($id, $clave)
    {
        include "Encriptador.php";

        try {

            $mail = $this->findMail($id);
            $body = $desencriptar($mail['body'], $clave, $mail['iv']);
        } catch (Exception $e) {
            return ["msg" => $e->getMessage(), "isDescrypted" => false];
        }

        return ["body" => $body, "msg" => "Desencriptado", "isDescrypted" => true];
    }

    function getFile($id)
    {
        $sql = $this->dbConn->prepare("SELECT * FROM files WHERE mail_id=:id");
        $sql->bindValue(':id', $id);
        $sql->execute();

        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    function setFile($mail_id)
    {

        $ruta_upload = '../Uploads/';

        $subir_archivo = $ruta_upload . $mail_id . basename($_FILES['file']['name']);

        if (move_uploaded_file($_FILES['file']['tmp_name'], $subir_archivo)) {
            return ['isUploaded'=>true, 'ruta' => $subir_archivo];
        } else {
            return ['isUploaded'=>false];
        }
    }
}

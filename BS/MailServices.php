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

        return  $sql->fetchAll();;
    }

    function findMail($id)
    {
        $sql = $this->dbConn->prepare("SELECT * FROM mails WHERE id=:id");
        $sql->bindValue(':id', $id);
        $sql->execute();

        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    function DeleteMail($id)
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
            $statement->bindValue(":id", $id_generated);
            $statement->bindValue(":iv", $iv);
            $statement->bindValue(":transmitter", $input['transmitter']);
            $statement->bindValue(":receiver", $input['receiver']);
            $statement->bindValue(":subject", $input['subject']);
            $statement->bindValue(":body", $body);
            $statement->bindValue(":has_files", $input['has_files']);
            $statement->bindValue(":delete_date", $input['delete_date']);

            $statement->execute();

            return ["isCreated" => true];
        } catch (PDOException $exception) {

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

        try{

        $mail = $this->findMail($id);
        $body = $desencriptar($mail['body'], $clave, $mail['iv']);

        }catch(Exception $e){
            return ["msg"=> $e->getMessage(), "isDescrypted"=>true];
        }

        return ["body"=>$body, "msg"=>"Desencriptado", "isDescrypted"=>true];
    }
}

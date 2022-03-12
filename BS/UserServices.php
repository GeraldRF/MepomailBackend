<?php

class UserServices
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

    function getAllUsers()
    {
        $sql = $this->dbConn->prepare("SELECT * FROM users");
        $sql->execute();

        $sql->setFetchMode(PDO::FETCH_ASSOC);

        return  $sql->fetchAll();;
    }

    function findUser($email)
    {
        $sql = $this->dbConn->prepare("SELECT * FROM users WHERE email=:email");
        $sql->bindValue(':email', $email);
        $sql->execute();

        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    function desactiveUser($email)
    {
        try {
            $email = $email;
            $statement = $this->dbConn->prepare("UPDATE users SET Active=1 WHERE email=:email");
            $statement->bindValue(':email', $email);
            $statement->execute();

            return true;
        } catch (PDOException $exception) {

            return false;
        }
    }

    function createUser($input)
    {

        $input['password'] = password_hash($input['password'], PASSWORD_DEFAULT);


        $sql = "INSERT INTO users 
                (email, password, name, last_name)
                VALUES
                (:email, :password, :name, :last_name)";

        try {

            $statement = $this->dbConn->prepare($sql);
            bindAllValues($statement, $input);

            $statement->execute();

            return ["isCreated" => true];
        } catch (PDOException $exception) {

            return ["isCreated" => false, "msg" => $exception->getMessage()];
        }
    }

    function updateUser($email, $input)
    {

        if (isset($input['password']))
            $input['password'] = password_hash($input['password'], PASSWORD_DEFAULT);


        $email = $email;
        $fields = getParams($input);

        $sql = "UPDATE users
          SET $fields
          WHERE email=:emailAnt";

        try {

            $statement = $this->dbConn->prepare($sql);
            bindAllValues($statement, $input);
            $statement->bindValue(":emailAnt", $email);

            $statement->execute();

            return ["isUpdated" => true];
        } catch (PDOException $exception) {

            return ["isUpdated" => false, "msg" => $exception->getMessage()];
        }
    }

    function checkUserLogin($email, $password)
    {

        try {

            $user = $this->findUser($email);

            if ($user != null) {

                if (password_verify($password, $user["password"])) {

                    return ["user" => $user, "isVerified" => true];
                } else {

                    return ["isVerified" => false, "msg" => "Upss, contraseña equivocada."];
                }
            } else {

                return ["isVerified" => false, "msg" => "El usuario no existe"];
            }
        } catch (Exception $exception) {

            return ["isVerified" => false, "msg" => $exception->getMessage()];
        }
    }
}

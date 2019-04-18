<?php
/**
 * Created by PhpStorm.
 * User: anisdhapa
 * Date: 2019-04-16
 * Time: 08:43
 */

class User
{

    public $id;
    public $username;
    public $email;
    public $password;

    public function __construct($id, $email, $username, $password)
    {
        $this->id = $id;
        $this->$username = $username;
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * @param $email
     * @return User class
     */
    public static function findUser($email, $conn) {
        // $email = sanitizeMySQL($conn, $email);

        $sql = "SELECT * FROM thr_users WHERE email = '$email'";
        $result = $conn->query($sql) or die($conn->error);
        $row = $result->fetch_assoc();
        return new User($row['id'], $row['email'], $row['username'], $row['password']);
    }
}
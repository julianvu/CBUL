<?php
/**
 * Created by PhpStorm.
 * User: anisdhapa
 * Date: 2019-04-15
 * Time: 20:17
 */


Class Authentication
{


    function sanitizeString($var)
    {
        $var = stripslashes($var);
        $var = strip_tags($var);
        $var = htmlentities($var);
        return $var;
    }


    /**
     * Sanitizes a string in preparation for database queries
     *
     * JV: This function really should be placed in a different file where similar
     * utility functions are. It's here for now because I need it to develop
     * without dependencies.
     *
     * @param $connection    MySQL connection
     * @param $var   String to sanitize
     * @return     Sanitized string
     */
    function sanitizeMySQL($var, $connection)
    {

        $var = $connection->real_escape_string($var);
        $var = sanitizeString($var);
        return $var;
    }


    /**
     * this functions should not be here.
     * @param $hn
     * @param $un
     * @param $pw
     * @param $db
     * @return mysqli
     */
    function databaseCreation($hn, $un, $pw, $db)
    {
        $conn = new mysqli($hn, $un, $pw);
        if ($conn->connect_error) die($conn->connect_error);
        $query = "CREATE DATABASE IF NOT EXISTS $db";
        if ($conn->query($query) === FALSE) {
            echo "Error in database: " . $conn->error;
        }
        echo "<br>";


        mysqli_select_db($conn, $db) or die($conn->error);
        $query = "CREATE TABLE IF NOT EXISTS userInfo(
      id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      email VARCHAR(320) NOT NULL,
      username VARCHAR(32) NOT NULL,
      password VARCHAR(32) NOT NULL 
    )";

        $result = $conn->query($query);
        if (!$result) die ("Database access failed: " . $conn->error);
        return $conn;
    }


    public function authenticate()
    {
        $userID = $this->findUser($email, $conn);
    }

    public function findUser($email, $conn) {
        // $email = sanitizeMySQL($conn, $email);

        $sql = "SELECT * FROM thr_users WHERE email = '$email'";
        $result = $conn->query($sql) or die($conn->error);
        $row = $result->fetch_assoc();
        return $row['id'];
    }

}


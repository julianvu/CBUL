<?php
/**
 * Created by PhpStorm.
 * User: anisd11
 * Date: 2019-04-20
 * Time: 13:19
 */


class utilities
{

    /**
     * Database creation
     *
     * Create database and table if not exists and return the instance of database.
     * @param $hn
     * @param $un
     * @param $pw
     * @param $db
     * @return mysqli instance of Database
     */
    public static function databaseCreation($hn, $un, $pw, $db)
    {
        $conn = new mysqli($hn, $un, $pw);
        if ($conn->connect_error) die($conn->connect_error);
        $query = "CREATE DATABASE IF NOT EXISTS $db";
        if ($conn->query($query) === FALSE) {
            echo "Error in database: " . $conn->error;
        }
        echo "<br>";
        mysqli_select_db($conn, $db) or die($conn->error);

//        WE ARE GOING TO REPLACE THIS WITH TABLE
//
//        $query = "CREATE TABLE IF NOT EXISTS USERFILE (
//            id INTEGER NOT NULL,
//            filename VARCHAR(64) NOT NULL,
//            content TEXT NOT NULL,
//            PRIMARY KEY (id, filename)
//        )";
//
//        $result = $conn->query($query);
//        if (!$result) die ("Database access failed: " . $conn->error);

        $query2 = "CREATE TABLE IF NOT EXISTS USER (
            id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY, 
            username VARCHAR(64) NOT NULL,
            email VARCHAR(64) NOT NULL,
            password VARCHAR(128) NOT NULL
        )";

        $result2 = $conn->query($query2);

        if (!$result2) die ("Database access failed: " . $conn->error);

        return $conn;
    }


    /**
     * Sanitize the string
     *
     * Sanitize the input string against htmlentities, strip tags and slashes
     *
     * @param $var
     * @return string Sanitized String
     */
    public static function sanitizeString($var)
    {
        $var = stripslashes($var);
        $var = strip_tags($var);
        $var = htmlentities($var);
        return $var;
    }

    /**
     * Sanitizes a string in preparation for database queries
     *
     * This function sanitizes the given string for database
     *
     * @param $conn     MySQL connection
     * @param $string   String to sanitize
     * @return mixed    Sanitized string
     */
    public static function sanitizeMySQL($connection, $var)
    {
        $var = $connection->real_escape_string($var);
        $var = utilities::sanitizeString($var);
        return $var;
    }

    /**
     * Hash the input
     *
     * Hash the given string using ripemd128
     * @param $string string to hash
     * @return string hashed string
     */
    public static function hashPassword($string){
        $salt1 = 'AB%43';
        $salt2 = 'AB%4323';
        return hash('ripemd128', $salt1.$string.$salt2);

    }

    /**
     * Get the User password
     *
     * Get the user password from USER Table in database
     * @param $id User Id
     * @param $conn Database Connection
     */
    public static function getUserPassword($id, $conn)
    {

        $query = "SELECT * FROM USER WHERE id = $id";
        $result = $conn->query(sprintf($query, $id));
        if (!$result) return;
        $row = $result->fetch_assoc() or die($conn->error);

        return $row['password'];

    }

    /**
     * Get the User ID from username
     *
     * Search the USER table and get the User ID from given username
     *
     * @param $username Username
     * @param $conn  Database
     * @return ID of User
     */
    public static function findUser($username, $conn) {
        // $email = sanitizeMySQL($conn, $email);

        $sql = "SELECT * FROM USER WHERE username = '$username'";
        $result = $conn->query($sql) or die($conn->error);
        $row = $result->fetch_assoc();
        return $row['id'];
    }


}
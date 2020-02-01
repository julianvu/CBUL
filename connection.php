<?php
/**
 * Created by PhpStorm.
 * User: zjeff
 * Date: 4/5/2019
 * Time: 3:55 PM
 */
    require_once 'login.php';

    $conn = new mysqli($hn, $un, $pw);
    if($conn->connect_error)die($conn->connect_error);

    $query = "CREATE DATABASE IF NOT EXISTS $db";
    if($conn->query($query) === TRUE)
    {
        echo "Database create successfully";
    }
    else
    {
        echo "Error creating database: " . $conn->error;
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
    if(!$result) die ("Database access failed: " .$conn->error);


    $conn->close();
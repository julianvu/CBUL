<?php
/**
 * Created by Julian Vu
 */

require_once "login.php";
require_once 'utilities.php';

session_start();
$user_id = '';
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];

}
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id();
    $_SESSION['initiated'] = 1;
}

if($user_id != '' && $_SESSION['check'] == hash('ripemd128', $_SERVER['REMOTE_ADDR'] .
        $_SERVER['HTTP_USER_AGENT'])) {
    $conn = utilities::databaseCreation($hn, $un, $pw, $db);

    echo <<< _END
        <head>
        <title>CBUL - Train a Model using EM</title>
        </head>
        <body>
        <form method="post" action="train_em.php">
        <h1>Let's Train a Model</h1>
            <br>
            Number of clusters <input type = "number" name = "cluster_count" min = "1" max = "10">
            <br>
            <br>
            Clusters built from which model <input type = "text" name = "model_name">
            <br>
            <br>
            <input type="submit" value="Go To Test">
        </form> 
        </body>
_END;

    if (isset($_POST["model_name"]) && isset($_POST["cluster_count"])) {
        begin_em($conn);
    }

    $conn->close();

} else {
    echo "You are not allowed to access this page without authentication";
}

function begin_em($conn) {
    $model_name = utilities::sanitizeMySQL($conn, $_POST["model_name"]);
    $cluster_count = utilities::sanitizeMySQL($conn, $_POST["cluster_count"]);
    if(!is_int($cluster_count) && $cluster_count > 10) die ("Cluster size is invalid");

    $data_array = extract_data($conn, $model_name);
    $size_m = sizeof($data_array);
    $size_n = 1;

//    $mean_array = randomize_array(0, 1, $cluster_count * $size_n);
}

function randomize_array($min, $max, $size) {
    $to_return = range($min, $max, 0.000001);
    $to_return = shuffle($to_return);
    return array_slice($to_return, mt_rand(0, sizeof($to_return), $size));
}

function extract_data($conn, $model_name) {
    $userID = $_SESSION["id"];
    $query = "SELECT * FROM em_data WHERE user_id = '$userID' AND model_name = '$model_name'";
    $result = $conn->query($query);
    if(!$result) die ("Database access for user plots failed");

    $result->data_seek(0);
    $row = $result->fetch_array(MYSQLI_NUM);
    return unserialize($row[0]);
}


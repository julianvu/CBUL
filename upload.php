<?php
/**
 * Created by PhpStorm.
 * User: julianvu
 * Date: 2019-04-15
 * Time: 07:59
 */
require_once "login.php";
require_once 'utilities.php';


/**
 * Handle file/text upload of a model.
 *
 * This function handles both file uploading and manual data entry into
 * the database. 
 *
 * @param $conn     MySQL connection
 */
function upload($conn) {
    if (isset($_POST["model_name"])) {
        $model_name = utilities::sanitizeMySQL($conn, $_POST["model_name"]);

        if (is_uploaded_file($_FILES["model_to_upload"]["tmp_name"])) {
            $temp_file = $_FILES["model_to_upload"]["name"];
            $uploaded_model_ext = pathinfo($temp_file, PATHINFO_EXTENSION);
            if ($uploaded_model_ext !== "txt") {
                echo "File type error - File not TXT";
                return;
            }

            move_uploaded_file($_FILES["model_to_upload"]["tmp_name"], $temp_file);
            $model_content = file_get_contents($temp_file);
            unlink($temp_file);
        }

        if (isset($_POST["model_content"]) && $_POST["model_content"] !== "") {
            $model_content = utilities::sanitizeMySQL($conn, $_POST["model_content"]);
        }

        $query = "INSERT INTO datasets VALUES" . "('$model_name', '$model_content')";
        $result = $conn->query($query);
        if (!$result) die ("Insertion failed: " . $conn->error);
    }
}
session_start();
$user_id = '';
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];

}
if (!isset($_SESSION['initiated']))
{
    session_regenerate_id();
    $_SESSION['initiated'] = 1;
}


if($user_id != '' && $_SESSION['check'] == hash('ripemd128', $_SERVER['REMOTE_ADDR'] .
        $_SERVER['HTTP_USER_AGENT'])) {
    $conn = utilities::databaseCreation($hn, $un, $pw, $db);
    upload($conn);

    echo <<< _END
        <html>
        <head>
        <title>CBUL - Upload a Model</title>
        </head>
        <body>
            <form method="post" action="upload.php" enctype="multipart/form-data">
                Choose a model from computer (.txt files only): <input type="file" name="model_to_upload">
                <br>
                or Enter data: <input type="text" name="model_content">
                <br>
                Model name: <input type="text" name="model_name">
                <input type="submit">
            </form>
        </body>
        </html>
_END;

    $conn->close();
}else{
    echo "You are not allowed to access this page without authentication";
}
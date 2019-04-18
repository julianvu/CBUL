<?php
/**
 * Created by PhpStorm.
 * User: julianvu
 * Date: 2019-04-15
 * Time: 07:59
 */
require_once "login.php";

/*
 * Begin database connection/creation section
 *
 * JV: This section was for me to develop this part of the project without dependencies
 * with other pages. This section can presumably be removed after authenticating the user
 * and ensuring that the database is created.
 */
$conn = new mysqli($hn, $un, $pw);
if($conn->connect_error)die($conn->connect_error);

$query = "CREATE DATABASE IF NOT EXISTS $db";
$result = $conn->query($query);
if(!$result) die ("Error creating database: " . $conn->error);
/*
 * End database connection/creation section
 */

/**
 * Sanitizes a string in preparation for database queries
 *
 * JV: This function really should be placed in a different file where similar
 * utility functions are. It's here for now because I need it to develop
 * without dependencies.
 *
 * @param $conn     MySQL connection
 * @param $string   String to sanitize
 * @return mixed    Sanitized string
 */
function mysql_fix_string($conn, $string) {
    if (get_magic_quotes_gpc()) {
        $string = stripslashes($string);
    }
    return $conn->real_escape_string($string);
}

/*
 * Create table containing datasets if it doesn't already exist.
 *
 * JV: For now, the table doesn't include the user ID because the user
 * authentication work hasn't been completed yet. Once that work is
 * finished, this file should have access to the user ID of the authenticated
 * user. This table stores ALL datasets, so we access all the datasets of
 * a particular user by looking up their user ID.
 */
mysqli_select_db($conn, $db) or die($conn->error);
$query = "CREATE TABLE IF NOT EXISTS datasets(
      model_name VARCHAR(32) NOT NULL,
      model_content TEXT NOT NULL
    )";
$result = $conn->query($query);
if(!$result) die ("Database access failed: " . $conn->error);

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
        $model_name = mysql_fix_string($conn, $_POST["model_name"]);

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
            $model_content = mysql_fix_string($conn, $_POST["model_content"]);
        }

        $query = "INSERT INTO datasets VALUES" . "('$model_name', '$model_content')";
        $result = $conn->query($query);
        if (!$result) die ("Insertion failed: " . $conn->error);
    }
}

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
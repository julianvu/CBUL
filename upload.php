<?php
/**
 * Created by PhpStorm.
 * User: julianvu
 * Date: 2019-04-15
 * Time: 07:59
 */
require_once "login.php";
require_once 'utilities.php';
require_once 'coordinate.php';

session_start();
$user_id = '';

$errorMessage = '';
if (isset($_SESSION['err_mess'])) {
    $errorMessage = $_SESSION['err_mess'];
    unset($_SESSION['err_mess']);
}
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];

}
if (!isset($_SESSION['initiated']))
{
    session_regenerate_id();
    $_SESSION['initiated'] = 1;
}
$conn = utilities::databaseCreation($hn, $un, $pw, $db);
if($user_id != '' && $_SESSION['check'] == hash('ripemd128', $_SERVER['REMOTE_ADDR'] .
        $_SERVER['HTTP_USER_AGENT'])) {
    upload($conn, $user_id);

    echo <<< _END
        <html>
        <head>
        <title>CBUL - Upload a Model</title>
        </head>
        <body>
            <form method="post" action="upload.php" enctype="multipart/form-data">
                EM Model <input type="radio" name="model_type" value="EM">
                K-Means Model <input type="radio" name="model_type" value="K-Means">
                <br>
                <br>
                <h2>Format input with: "(" + 'x value' + "," + 'y value' + + ")"</h2>
                <h4>Such as (1, 1) or (1 , 1)</h4>
                <h4>This can handle multiple coordinate inputs</h4>
                <h4>Input X and Y values must fall in between 0 to 500</h4>
                <br>
                Choose a model from computer (.txt files only): <input type="file" name="model_to_upload">
                <br>
                <br>
                or Enter data: <input type="text" name="model_content">
                <br>
                <br>
                Model name: <input type="text" name="model_name">
                <input type="submit" value="Enter">
                 <h4 class="form-signin-heading">$errorMessage</h4>
                <br>
                <br>
            </form>
            
            <form method="post" action="train.php">
                <input type="submit" value="Go To Train">
            </form>
            <form method="post" action="train_em.php">
                <input type="submit" value="Go to EM Training">
            </form>
            
            
        </body>
        </html>
_END;
}else{
    utilities::mysql_fatal_error("You are not allowed to access this page without authentication", $conn);
}
$conn->close();

function readFileContents($conn, $model_name, $user_id)
{
    if (is_uploaded_file($_FILES["model_to_upload"]["tmp_name"]) && file_exists($_FILES["model_to_upload"]["tmp_name"])) {
        $temp_file = $_FILES["model_to_upload"]["name"];

        if ($_FILES['model_to_upload']['type'] !== "text/plain") {
            utilities::mysql_fatal_error("Not a text file", $conn);
            return;
        }

        $name = $_FILES["model_to_upload"]["tmp_name"];
        $fp = fopen($name, 'r');
        $content = fread($fp, filesize($name));
        fclose($fp);
        if (isset($_POST["model_type"])) {
            if ($_POST["model_type"] == "K-Means") {
                $lines = explode("\n", $content);
                foreach ($lines as $line)
                {
                    $sanitized_line = utilities::sanitizeMySQL($conn, $line);
                    storeLine($sanitized_line, $conn, $model_name, $user_id);
                }
            }
            else {
                $content = utilities::sanitizeMySQL($conn, $content);
                store_EM_model($conn, $content);
            }
        }
    }
}

/**
 * Handle file/text upload of a model.
 *
 * This function handles both file uploading and manual data entry into
 * the database. 
 *
 * @param $conn     MySQL connection
 * @param $user_id  User Id passed
 */
function upload($conn, $user_id) {
    $model_name = "";
    if (isset($_POST["model_name"])) {
        $model_name = utilities::sanitizeMySQL($conn, $_POST["model_name"]);
    }

    if (isset($_POST["model_content"]) && $model_name != "") {
        $model_content = utilities::sanitizeMySQL($conn, $_POST["model_content"]);
        storeLine($model_content, $conn, $model_name, $user_id);
    }

    if(isset($_POST['model_name']))
    {
        readFileContents($conn, $model_name, $user_id);
    }
}

function store_EM_model($conn, $sanitized_content) {
    $lines = explode("n", $sanitized_content);
    $matrix = array();
    for ($i = 0; $i < sizeof($lines); ++$i) {
        $line = explode(",", $lines[$i]);
        $matrix[$line[0]] = $line[1];
    }
    $serialized_array = serialize($matrix);
    $model_name = utilities::sanitizeMySQL($conn, $_POST["model_name"]);
    $user_id = $_SESSION['id'];
    $query = "INSERT INTO em_data VALUES" . "('$serialized_array', '$user_id', '$model_name')";
    $result = $conn->query($query);
    if (!$result) die("EM insertion failed" . $conn->error);
}

function storeLine($value, $conn, $modelName, $user_id)
{
    $arr_coordinates = [];
    $arrX = utilities::create_coordinate_x($value);
    $arrY = utilities::create_coordinate_Y($value);

    if (sizeof($arrX) == sizeof($arrY)) {
        for($i = 0; $i < sizeof($arrX); $i++)
        {
            $coordinate = new coordinate($arrX[$i], $arrY[$i]);
            array_push($arr_coordinates, $coordinate);
        }
    } else die("The number of x inputs and y inputs do not match");


    $result = null;
    for ($i = 0; $i < sizeof($arr_coordinates); $i++) {
        $x_value = $arr_coordinates[$i]->get_x();
        $y_value = $arr_coordinates[$i]->get_y();
        $query = "INSERT INTO userDataPlots (x, y, userId, modelName) VALUES('$x_value', '$y_value', '$user_id', '$modelName')";
        $result = $conn->query($query);
        if (!$result) utilities::mysql_fatal_error("Insertion for ". " $x_value " . ", $y_value" . " failed", $conn);
    }
}
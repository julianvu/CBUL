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
            
        </body>
        </html>
_END;
    $conn->close();
}else{
    utilities::mysql_fatal_error("You are not allowed to access this page without authentication", $conn);
}

function readFileContents($conn, $model_name)
{
    if (is_uploaded_file($_FILES["model_to_upload"]["tmp_name"])) {
        $temp_file = $_FILES["model_to_upload"]["name"];
        $uploaded_model_ext = pathinfo($temp_file, PATHINFO_EXTENSION);

        if ($_FILES['model_to_upload']['type'] !== "text/plain") {
            utilities::mysql_fatal_error("Not a text file", $conn);
            return;
        }

        $name = $_FILES["model_to_upload"]["tmp_name"];
        $fp = fopen($name, 'r');
        $content = fread($fp, filesize($name));
        $lines = explode("\n", $content);
        fclose($fp);
        foreach ($lines as $line)
        {
            $sanitized_line = utilities::sanitizeMySQL($conn, $line);
            storeLine($sanitized_line, $conn, $model_name);
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
 */
function upload($conn) {
    $model_name = "";
    if (isset($_POST["model_name"])) {
        $model_name = utilities::sanitizeMySQL($conn, $_POST["model_name"]);
    }

    if (isset($_POST["model_content"]) && $model_name != "") {
        $model_content = utilities::sanitizeMySQL($conn, $_POST["model_content"]);
        storeLine($model_content, $conn, $model_name);
    }

    if(isset($_POST['model_name']))
    {
        readFileContents($conn, $model_name);
    }
}

function storeLine($value, $conn, $modelName)
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

    $user_id = $_SESSION['id'];
    for ($i = 0; $i < sizeof($arrX); $i++) {
        $x_value = $arr_coordinates[$i]->get_x();
        $y_value = $arr_coordinates[$i]->get_y();
        $query = "INSERT INTO userDataPlots (x, y, userId, modelName) VALUES('$x_value', '$y_value', '$user_id', '$modelName')";
        $result = $conn->query($query);
        if (!$result) die("insert of file plot failed" . $conn->error);
    }
}

function create_coordinate_list($value, &$arrX, &$arrY)
{
    //for x values
    if(preg_match_all("/\(\s*\d*?\s*\,/", $value, $xs))
    {
        foreach ($xs as $row)
        {
            for($i = 0; $i < sizeof($row); $i++)
            {
                $arrX[$i] = substr($row[$i], 1, -1);
            }
        }
    }
    if(preg_match_all("/\,\s*\d*?\s*\)/", $value, $ys))
    {
        foreach ($ys as $row)
        {
            for($i = 0; $i < sizeof($row); $i++)
            {
                $arrY[$i] = substr($row[$i], 1, -1);
            }
        }
    }
}
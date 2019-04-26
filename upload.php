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
                <br>
                or Enter data: <input type="text" name="model_content">
                <br>
                <br>
                Model name: <input type="text" name="model_name">
                <input type="submit">
                <br>
                <br>
                Number of clusters <input type = "number" id = "clusterNumber" min = "1" max = "10">
                <br>
            </form>
        </body>
        </html>
_END;
    $conn->close();
}else{
    echo "You are not allowed to access this page without authentication";
}
function readFileContents($conn)
{
    if (is_uploaded_file($_FILES["model_to_upload"]["tmp_name"])) {
        $temp_file = $_FILES["model_to_upload"]["name"];
        $uploaded_model_ext = pathinfo($temp_file, PATHINFO_EXTENSION);
        if ($uploaded_model_ext !== "txt") {
            echo "File type error - File not TXT";
            return;
        }

        $name = $_FILES["model_to_upload"]["tmp_name"];
        $fp = fopen($name, 'r');
        $content = fread($fp, filesize($name));
        $lines = explode("\n", $content);
        fclose($fp);
        foreach ($lines as $line)
        {
            utilities::sanitizeMySQL($conn, $line);
            storeLine($line, $conn);
        }
    }

    if(isset($_POST["model_content"]))
    {
        utilities::sanitizeMySQL($conn, $_POST["model_content"]);
        storeLine($_POST["model_content"], $conn);
    }
}

function upload($conn) {
    if (isset($_POST["model_name"])) {
        $model_name = utilities::mysql_fix_string($conn, $_POST["model_name"]);

        readFileContents($conn);

        $model_content = "";
        if (isset($_POST["model_content"]) && $_POST["model_content"] !== "") {
            $model_content = utilities::mysql_fix_string($conn, $_POST["model_content"]);
        }
    }
}

function storeLine($value, $conn)
{

    $arrX = [];
    //for x values
    if(preg_match_all("/\(\d*?\,/", $value, $xs))
    {
        foreach ($xs as $row)
        {
            for($i = 0; $i < sizeof($row); $i++)
            {
                $arrX[$i] = substr($row[$i], 1, -1);
            }
        }
    }

    $arrY = [];
    if(preg_match_all("/\,\d*?\)/", $value, $ys))
    {
        foreach ($ys as $row)
        {
            for($i = 0; $i < sizeof($row); $i++)
            {
                $arrY[$i] = substr($row[$i], 1, -1);
            }
        }
    }
    $user_id = $_SESSION['id'];
    if(sizeof($arrX) == sizeof($arrY))
    {
        for($i = 0; $i < sizeof($arrX); $i++)
        {
            $query = "INSERT INTO userDataPlots (x, y, username) VALUES('$arrX[$i]', '$arrY[$i]', '$user_id')";
            $result = $conn->query($query);
            if (!$result) die("insert of file plot failed".$conn->error);
        }

    }else die("Bad Data");
}
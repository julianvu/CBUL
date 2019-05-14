<?php
/**
 * Created by PhpStorm.
 * User: zjeff
 * Date: 5/7/2019
 * Time: 6:31 PM
 */
require_once "utilities.php";
require_once "login.php";
require_once "coordinate.php";

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

    if(isset($_POST['model_name']))
    {
        $nearest_cluster = calculateNearestCentroid($conn, $user_id);
        echo "Nearest cluster centroid is is where X: " . $nearest_cluster->get_X() . " Y: " . $nearest_cluster->get_Y();
    }

    echo <<< _END
        <html>
        <head>
        <title>CBUL - Test</title>
        </head>
        <body>
            <form method="post" action="test.php" enctype="multipart/form-data">
                Model name of Clusters: <input type="text" name="model_name">
                <br>
                <br>
                Enter Data and See which Cluster: <input type="text" name="test_coordinate">
                <br>
                <br>
                <input type="submit" value="Find Closest Centroid">
            </form>   
        </body>
        </html>
_END;
    $conn->close();
}else{
    echo "You are not allowed to access this page without authentication";
}

function calculateNearestCentroid($conn, $userId)
{
    $coordinate = extractUserTestData($conn);
    $model_name_choice = $_POST['model_name'];
    $query = "SELECT * FROM centroids WHERE userId = '$userId' AND modelName = '$model_name_choice'";
    $result = $conn->query($query);
    $rows = $result->num_rows;
    $centroids_coordinates = [];

    $minDistance = PHP_INT_MAX;
    $nearest_cluster = null;
    for($j = 0; $j < $rows; ++$j)
    {
        $result->data_seek($j);
        $row = $result->fetch_array(MYSQLI_NUM);
        $centroid_coordinate = new coordinate($row[3], $row[4]);
        $distance = utilities::get_euclidean_distance($coordinate, $centroid_coordinate);
        if($distance < $minDistance)
        {
            $nearest_cluster = $centroid_coordinate;
            $minDistance = $distance;
        }
    }

    if($nearest_cluster == null) utilities::mysql_fatal_error("No closest clusters check data", $conn);
    $result->close();
    return $nearest_cluster;
}

function extractUserTestData($conn)
{
    if(isset($_POST["test_coordinate"]))
    {
        $input_coordinate = $_POST["test_coordinate"];
        $sanitized = utilities::sanitizeMySQL($conn, $input_coordinate);
        preg_match("/\(\s*\d*?\s*\,/", $sanitized, $x);
        preg_match("/\,\s*\d*?\s*\)/", $sanitized, $y);
    }else{
        utilities::mysql_fatal_error("Input is incorrect", $conn);
    }
    $x_coordinate = substr($x[0], 1, -1);
    $y_coordinate = substr($y[0], 1, -1);
    $coordinate = new coordinate($x_coordinate, $y_coordinate);
    return $coordinate;
}
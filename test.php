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

    if(isset($_POST['model_name']) && isset($_POST["test_coordinate"]) && $_POST["test_coordinate"] !== "")
    {
        $model_name = utilities::sanitizeMySQL($conn, $_POST['model_name']);
        $input_coordinate = $_POST["test_coordinate"];
        $k = $_POST["cluster_number"];
        if(is_int($k))
        {
            utilities::mysql_fatal_error("Cluster number input must be a number", $conn);
        }
        $test_coordinate = utilities::sanitizeMySQL($conn, $input_coordinate);
        $coordinates = str_to_coordinates($conn, $test_coordinate);
        $nearest_clusters = calculateNearestCentroid($conn, $coordinates, $user_id, $model_name, $k);
        if($nearest_clusters != null)
        {
            for($i = 0; $i < sizeof($nearest_clusters); $i++)
            {
                echo "Coordinate (" . $coordinates[$i]->get_X() . ", " . $coordinates[$i]->get_Y() . ")'s Nearest Centroid is where X: " . $nearest_clusters[$i]->get_X() . " Y: " . $nearest_clusters[$i]->get_Y() . "<br>";

            }
        }
    }

    if(isset($_POST['model_name']) && is_uploaded_file($_FILES["test_file"]["tmp_name"]))
    {
        $model_name = utilities::sanitizeMySQL($conn, $_POST['model_name']);
        $k = $_POST["cluster_number"];
        $coordinates_str = readFileContents($conn);
        $coordinates = str_to_coordinates($conn, $coordinates_str);
        $nearest_clusters = calculateNearestCentroid($conn, $coordinates, $user_id, $model_name, $k);
        if($nearest_clusters != null)
        {
            for($i = 0; $i < sizeof($nearest_clusters); $i++)
            {
                echo "Coordinate (" . $coordinates[$i]->get_X() . ", " . $coordinates[$i]->get_Y() . ")'s Nearest Centroid is where X: " . $nearest_clusters[$i]->get_X() . " Y: " . $nearest_clusters[$i]->get_Y() . "<br>";

            }
        }
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
                Choose a file to See Which Clusters (.txt files only): <input type="file" name="test_file">
                <br>
                OR
                <br>
                Enter Data and See which Clusters: <input type="text" name="test_coordinate">
                <br>
                <br>
                <br>
                Number of Clusters for this Model: <input type="number" name="cluster_number" min = "1" max = "10">
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

function calculateNearestCentroid($conn, $coordinates, $user_id, $model_name, $k)
{
    $nearest_clusters = [];
    $query = "SELECT * FROM centroids WHERE userId = '$user_id' AND modelName = '$model_name' AND k = '$k'";
    $result = $conn->query($query);
    $rows = $result->num_rows;

    if($rows == 0)
    {
        utilities::mysql_fatal_error("No data found on these specifications", $conn);
        return null;
    }
    if($coordinates == null)
    {
        utilities::mysql_fatal_error("No test coordinates found", $conn);
        return null;
    }

    for($i = 0; $i < sizeof($coordinates); $i++)
    {
        $minDistance = PHP_INT_MAX;
        $nearest_cluster = null;
        for($j = 0; $j < $rows; ++$j)
        {
            $result->data_seek($j);
            $row = $result->fetch_array(MYSQLI_NUM);
            $centroid_coordinate = new coordinate($row[3], $row[4]);
            $distance = utilities::get_euclidean_distance($coordinates[$i], $centroid_coordinate);
            if($distance < $minDistance)
            {
                $nearest_cluster = $centroid_coordinate;
                $minDistance = $distance;
            }
        }
        $nearest_clusters[$i] = $nearest_cluster;
    }
    $result->close();
    return $nearest_clusters;
}

function readFileContents($conn)
{
    if (file_exists($_FILES["test_file"]["tmp_name"])) {
        if ($_FILES['test_file']['type'] !== "text/plain") {
            utilities::mysql_fatal_error("Not a text file", $conn);
            return;
        }

        $name = $_FILES["test_file"]["tmp_name"];
        $fp = fopen($name, 'r');
        $content = fread($fp, filesize($name));
        $lines = explode("\n", $content);
        fclose($fp);

        $line_str = "";
        foreach ($lines as $line)
        {
            $sanitized_line = utilities::sanitizeMySQL($conn, $line);
            $line_str .= $sanitized_line;
        }
        return $line_str;
    }
}

function str_to_coordinates($conn, $test_coordinate)
{
    $x = utilities::create_coordinate_x($test_coordinate);
    $y = utilities::create_coordinate_y($test_coordinate);
    $coordinates = [];
    if(sizeof($x) == sizeof($y)) {
        for ($i = 0; $i < sizeof($x); $i++) {
            $coordinates[$i] = new coordinate($x[$i], $y[$i]);
        }
        return $coordinates;
    }
    else{
        return null;
    }
}
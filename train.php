<?php
/**
 * Created by PhpStorm.
 * User: zjeff
 * Date: 5/3/2019
 * Time: 8:14 PM
 */

require_once "login.php";
require_once 'utilities.php';
require_once "coordinate.php";
require_once "centroid.php";

define("USER_DATA_PLOTS_COL_NUM", 4);
define("K_MEANS_ITERATIONS", 50);
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

    if(isset($_POST['model_name_choice']) && isset($_POST['cluster_number']))
    {
        $k = utilities::sanitizeMySQL($conn, $_POST['cluster_number']);

        k_means($conn, $user_id, $k);
    }

    echo <<< _END
        <html>
        <head>
        <title>CBUL - Train a Model</title>
        </head>
        <body>
        <form method="post" action="train.php">
        <h1>Lets Train a Model</h1>
            <br>
            Number of clusters <input type = "number" name = "cluster_number" min = "1" max = "10">
            <br>
            <br>
            Clusters built from which model <input type = "text" name = "model_name_choice">
            <br>
            <br>
            <input type="submit" value="Calculate Cluster Centroids">
        </form>
        <form method="post" action="test.php">
        <h1>Test some data</h1>
            <input type="submit" value="Test Data">
        </form>
        </body> 
        </html>
_END;

    $conn->close();
}else{
    echo "You are not allowed to access this page without authentication";
}


function k_means($conn, $user_id, $clusterNumber)
{
    $model_name = utilities::sanitizeMySQL($conn, $_POST['model_name_choice']);

    $iteration = 0;
    $coordinates = extract_data($conn, $clusterNumber, $model_name);
    if($coordinates == null)
    {
        return;
    }
    $centroids = initialize_k_means($clusterNumber);
    foreach ($centroids as $centroid)echo "Before " . $centroid->pretty_printing();
    while($iteration < K_MEANS_ITERATIONS)
    {
        calculate_nearest_centroids($centroids, $coordinates);
        relocate_by_classification($coordinates, $centroids);
        $iteration++;
    }

    $query = "DELETE FROM centroids WHERE userId = '$user_id' AND modelName = '$model_name' AND k = '$clusterNumber'";
    $result = $conn->query($query);
    if(!$result) utilities::mysql_fatal_error("Can not delete previous centroids", $conn);
    foreach ($centroids as $centroid)
    {
        echo "After " . $centroid->pretty_printing();
        $centroidX = $centroid->get_X();
        $centroidY = $centroid->get_Y();
        $query = "INSERT INTO centroids (userId, modelName, k, centroidX, centroidY) VALUES ('$user_id', '$model_name', '$clusterNumber','$centroidX', '$centroidY')";
        $result = $conn->query($query);
        if(!$result) utilities::mysql_fatal_error("Can not insert centroids", $conn);
    }
    //$result->close();
}

function relocate_by_classification(&$coordinates, &$centroids)
{
    foreach($coordinates as $coordinate)
    {
        //This is the centroid
        $centroid_object = $coordinate->get_nearest_centroid_coordinate();
        $centroid_object->new_classified($coordinate);
    }
    foreach($centroids as $centroid)
    {
        $centroid->relocate_centroid();
    }
}

function calculate_nearest_centroids(&$centroids, &$coordinates)
{
    //Set closest centroid for every coordinate
    foreach($coordinates as $coordinate)
    {
        //Compare with centroid
        $min_distance = PHP_INT_MAX;
        $closest_centroid = null;
        foreach($centroids as $centroid)
        {
            $distance_from_centroid = utilities::get_euclidean_distance($coordinate, $centroid);
            if($distance_from_centroid < $min_distance)
            {
                $min_distance = $distance_from_centroid;
                $closest_centroid = $centroid;
            }
        }
        $coordinate->set_nearest_centroid_coordinate($closest_centroid);
    }
}

function extract_data($conn, $cluster_number, $model_name_choice)
{
    if(!is_int($cluster_number) && $cluster_number > 10)die("Cluster size is invalid");

    $userId = $_SESSION['id'];
    $query = "SELECT * FROM userDataPlots WHERE userId = '$userId' AND modelName = '$model_name_choice'";
    $result = $conn->query($query);
    $rows = $result->num_rows;
    if($rows == 0)
    {
        utilities::mysql_fatal_error("No data found on these specifications", $conn);
        return null;
    }
    if(!$result)die("Database access for user plots");

    $coordinates = [];
    for($j = 0; $j < $rows; ++$j)
    {
        $result->data_seek($j);
        $row = $result->fetch_array(MYSQLI_NUM);
        $coordinate = new coordinate($row[0], $row[1]);
        array_push($coordinates, $coordinate);
    }
    //$result->close();
    return $coordinates;
}

function initialize_k_means($k)
{
    $centroids = [];
    for($i = 0; $i < $k; $i++)
    {
        $randX = mt_rand(0, 500);
        $randY = mt_rand(0, 500);
        $centroid_object = new centroid($randX, $randY);
        array_push($centroids, $centroid_object);
    }
    return $centroids;
}
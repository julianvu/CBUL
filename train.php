<?php
/**
 * Created by PhpStorm.
 * User: zjeff
 * Date: 5/3/2019
 * Time: 8:14 PM
 */

require_once "login.php";
require_once 'utilities.php';
require "coordinate.php";
require "centroid.php";

define("USER_DATA_PLOTS_COL_NUM", 4);
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
        k_means($conn);
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
            <input type="submit" value="Go To Test">
        </form>
        </body> 
        </html>
_END;

    $conn->close();
}else{
    echo "You are not allowed to access this page without authentication";
}


function k_means($conn)
{
    $coordinates = [];
    $centroids = [];
    
    extract_data($coordinates, $conn);
    initialize_k_means($centroids);
    calculate_nearest_centroids($centroids, $coordinates);
    relocate_by_classification($coordinates, $centroids);
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
        echo "Before " . $centroid->pretty_printing();
        $centroid->relocate_centroid();
        echo "After " . $centroid->pretty_printing();
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
            $distance_from_centroid = get_euclidean_distance($coordinate, $centroid);
            if($distance_from_centroid < $min_distance)
            {
                $min_distance = $distance_from_centroid;
                $closest_centroid = $centroid;
            }
        }
        $coordinate->set_nearest_centroid_coordinate($closest_centroid);
    }
}

function get_euclidean_distance($coordinate1, $coordinate2)
{
    $x_diff_pow = pow($coordinate1->get_x() - $coordinate2->get_x(), 2);
    $y_diff_pow = pow($coordinate1->get_y() - $coordinate2->get_y(), 2);
    return sqrt($x_diff_pow + $y_diff_pow);
}

function extract_data(&$coordinates, $conn)
{

    $model_name_choice = utilities::sanitizeMySQL($conn, $_POST["model_name_choice"]);
    $cluster_number = $_POST["cluster_number"];
    if(!is_int($cluster_number) && $cluster_number > 10)die("Cluster size is invalid");

    $userId = $_SESSION['id'];
    $query = "SELECT * FROM userDataPlots WHERE userId = '$userId' AND modelName = '$model_name_choice'";
    $result = $conn->query($query);
    if(!$result)die("Database access for user plots");

    $rows = $result->num_rows;
    for($j = 0; $j < $rows; ++$j)
    {
        $result->data_seek($j);
        $row = $result->fetch_array(MYSQLI_NUM);
        $coordinate = new coordinate($row[0], $row[1]);
        array_push($coordinates, $coordinate);
    }
}

function initialize_k_means(&$centroid)
{
    for($i = 0; $i < $_POST["cluster_number"]; $i++)
    {
        $randX = mt_rand(0, 500);
        $randY = mt_rand(0, 500);
        $centroid_object = new centroid($randX, $randY);
        array_push($centroid, $centroid_object);}
}
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
        <form method="post" action="test_em.php">
        <h1>Let's Train a Model</h1>
            <br>
            Number of clusters <input type = "number" name = "cluster_count" min = "1" max = "10">
            <br>
            <br>
            Clusters built from which model <input type = "text" name = "model_name">
            <br>
            <br>
        <input type="submit" value="Go To EM Testing">

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

        // Extract Data from Database
//    $data_array = extract_data($conn, $model_name);
//    $size_m = sizeof($data_array);
//    $size_n = sizeof($data_array[0]);

        // Use randomly generated data
        $data_array = generate_data(100);
    }

    /**
     * Generates data
     *
     * This function generates the same data every time for reproducibility. The proper implementation would
     * actually generate random values.
     * @param $number_of_points     Number of Points per cluster
     */
    function generate_data($number_of_points) {
        $mu1 = array(0, 5);
        $mu2 = array(5, 0);
        $sigma1 = array(
            array(2, 0),
            array(0, 3),
        );
        $sigma2 = array(
            array(4, 0),
            array(0, 1),
        );


    }

function train_EM($data_input, $params_input) {
    $shift = PHP_FLOAT_MAX;
    $epsilon = 0.001; // precision

    $data = $data_input;
    $params = $params_input;

    for ($i = 0; $shift > $epsilon; ++$i) {
        // Expectation Step
        $data_from_e_step = expectation($data, $params);

        // Maximization Step
        $params_from_m_step = maximization($data_from_e_step, $params);

        // Re-calculate shift (distance/error) from previous set of parameters
        $shift = calc_error($params, $params_from_m_step);

        $data = $data_from_e_step;
        $params = $params_from_m_step;
    }
    return array($data, $params);
}

function expectation($data_input, $params_input) {
    $data = $data_input;
    for ($i = 0; $i < sizeof($data[0]); ++$i) {
        $x = $data[$i][0];
        $prob_cluster1 = prob($x, $params_input["mu1"], $params_input["sigma1"], ($params_input["lambda"])[0][0]);
        $prob_cluster2 = prob($x, $params_input["mu2"], $params_input["sigma2"], ($params_input["lambda"])[0][1]);

        if ($prob_cluster1 > $prob_cluster2) {
            $data[$i][3] = 1;
        }
        else {
            $data[$i][3] = 2;
        }
    }
    return $data;
}

function prob($point, $mu, $sigma, $lambda) {
    $prob = $lambda;
    for ($i = 0; $i < sizeof($point); ++$i) {
        $prob = $prob * normpdf($point[0][$i], $mu[0][$i], $sigma[$i][$i]);
    }
    return $prob;
}

function normpdf($x, $mean, $sigma) {
    $z = ($x - $mean) / $sigma;
    $y = (1.0 / ($sigma * sqrt(2.0 * pi()))) * exp(-0.5 * $z * $z);
    return doubleval($y);
}

function maximization($data_input, $params_input) {
    $data = $data_input;
    $params = $params_input;

    $points_in_cluster1 = find_clusters($data, 1);
    $points_in_cluster2 = find_clusters($data, 2);

    $percent_cluster1 = sizeof($points_in_cluster1) / sizeof(data);
    $percent_cluster2 = 1.0 - $percent_cluster1;

    $params["lambda"] = [$percent_cluster1, $percent_cluster2];

    $mu1_1_points = get_points($points_in_cluster1, 0);
    $mu1_2_points = get_points($points_in_cluster1, 1);
    $mu2_1_points = get_points($points_in_cluster2, 0);
    $mu2_2_points = get_points($points_in_cluster2, 1);

    $mu1_1 = array_sum($mu1_1_points) / sizeof($mu1_1_points);
    $mu1_2 = array_sum($mu1_2_points) / sizeof($mu1_2_points);
    $mu2_1 = array_sum($mu2_1_points) / sizeof($mu2_1_points);
    $mu2_2 = array_sum($mu2_2_points) / sizeof($mu2_2_points);

    $params["mu1"] = array($mu1_1, $mu1_2);
    $params["mu2"] = array($mu2_1, $mu2_2);

    $sigma1_1 = standard_deviation($mu1_1_points);
    $sigma1_2 = standard_deviation($mu1_2_points);
    $sigma2_1 = standard_deviation($mu2_1_points);
    $sigma2_2 = standard_deviation($mu2_2_points);

    $params["sigma1"] = array(
        array($sigma1_1, 0),
        array(0, $sigma1_2),
    );

    $params["sigma2"] = array(
        array($sigma2_1, 0),
        array(0, $sigma2_2),
    );

    return $params;
}

function get_points($cluster_array, $cluster_number) {
    $to_return = array();
    foreach ($cluster_array as $array) {
        array_push($to_return, $array[$cluster_number]);
    }
    return $to_return;
}

function standard_deviation($data) {
    $count = sizeof($data);
    $variance = 0.0;
    $mean = array_sum($data)/$count;

    foreach ($data as $i) {
        $variance += pow(($i - $mean), 2);
    }

    return (float)sqrt($variance/$count);
}

function find_clusters($data, $cluster_number) {
    $to_return = array();
    for ($i = 0; $i < sizeof($data); ++$i) {
        if ($data[$i][2] == $cluster_number) {
            array_push($to_return, $data[$i][2]);
        }
    }
    return $to_return;
}

function calc_error($old_params, $new_params) {
    $mu1_rss = pow(($old_params["mu1"])[0][0] - ($new_params["mu1"])[0][0], 2);
    $mu2_rss = pow(($old_params["mu1"])[1][1] - ($new_params["mu1"])[1][1], 2);
    return (float)sqrt($mu1_rss + $mu2_rss);
}

function norm($vector) {

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


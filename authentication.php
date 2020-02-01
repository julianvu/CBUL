<?php
/**
 * Created by Anis Dhapa
 */


require_once 'login.php';
require_once 'utilities.php';

//start the session
session_start();

/**
 * Authenticate the User
 *
 * Authenticate the user based on username and password. First, find the
 * user id based on username and compare user password with input password
 *
 * @param $conn database
 */
function authenticate($conn)
{

    $username = utilities::sanitizeMySQL($conn, $_POST['inputUsername']);
    $password = utilities::sanitizeMySQL($conn, $_POST['inputPassword']);

    $user_id = utilities::findUser($username, $conn);


    //check user with given email id exists and check the password is correct or not
    if($user_id == null || !isPassword($user_id,$password,$conn))
    {
        loginfail("invalid user email or password");
    }

    loginUser($user_id);
}


/**
 * Set the error message and refresh the page with error shown on the page
 *
 * @param $msg error message
 */
function loginfail($msg)
{
    $_SESSION['err_mess'] = $msg;
    header("Location: sign-in.php");
    die("Dieing");

}

/**
 * Validate the input password with User password
 *
 * Validate hashed input password with User hashed password store in database
 * @param $id User id
 * @param $inputPassword input password
 * @param $conn Database
 * @return bool
 */
function isPassword($id, $inputPassword, $conn){
    $query = "SELECT * FROM USER WHERE id = $id";
    $result = $conn->query(sprintf($query, $id));
    if (!$result) return;
    $row = $result->fetch_assoc() or die($conn->error);
    $result->close();
    return ($row['password'] === utilities::hashPassword($inputPassword,$row['salty'], $row['saltier']));
}

/**
 * Set the Session for User
 *
 * @param $id User id
 */
function loginUser($id)
{
    $_SESSION['check'] = hash('ripemd128', $_SERVER['REMOTE_ADDR'] .$_SERVER['HTTP_USER_AGENT']);
    $_SESSION['initiated'] = true;
    $_SESSION['id'] = $id;
    $_SESSION['isLogged'] = true;
    ini_set('session.gc_maxlifetime', 60 * 60 * 24);
    header("Location:upload.php");
    die();
}


$conn = utilities::databaseCreation($hn,$un,$pw,$db);
authenticate($conn);
$conn->close();
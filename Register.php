<?php
/**
 * Created by PhpStorm.
 * User: anisd11
 * Date: 2019-04-20
 * Time: 21:03
 */

require_once 'login.php';
require_once 'utilities.php';

session_start();

/**
 * Register new user into database
 *
 * Check if user has an account already or not. If user do not have an
 * account, It creates the new user in the USER table.
 * @param $conn Database
 */
function register($conn)
{
    $username = utilities::sanitizeMySQL($conn, $_POST['inputUsername']);
    $email = utilities::sanitizeMySQL($conn, $_POST['inputEmail']);
    $password = utilities::sanitizeMySQL($conn, $_POST['inputPassword']);

    $user_id = utilities::findUser($username, $conn);

    //
    if($user_id == null && $password == null && $email == null && $user_id == '' && $password == '' && $email == ''){
        $_SESSION['err_mess'] = "Username, Email and password field cannot be empty";
        header("Location: sign-up.php");

    } else {

        //check if user has an account or not with given username
        if ($user_id == null) {
            addUser($username, $email, $password, $conn);
            $_SESSION['err_mess'] = "successfully created new account";
            header("Location: sign-in.php");

        } else {
            $_SESSION['err_mess'] = "Username already exists. Choose another username that does not have an account";
            header("Location: sign-up.php");

        }
    }

}


/**
 * add user to database
 *
 * Add the user into USER table in database
 * @param $username
 * @param $email
 * @param $password
 * @param $conn
 */
function addUser($username,$email, $password, $conn)
{
    $hashPassword = utilities::hashPassword($password);
    $query = "INSERT INTO USER (username,email,password) VALUES('$username','$email','$hashPassword')";
    $result = $conn->query($query);
    if (!$result) die("insert failed".$conn->error);

}


$conn = utilities::databaseCreation($hn,$un,$pw,$db);
register($conn);


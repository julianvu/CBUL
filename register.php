<?php
/**
 * Created by Anis Dhapa
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
    if($username == null || $password == null || $email == null || $username == '' || $password == '' || $email == ''){
        $_SESSION['err_mess'] = "Username, Email and password field cannot be empty";
        header("Location: sign-up.php");

    } else {
        $error = "";
        $error .= validateName($username);
        $error .= validatePassword($password);
        //check if user has an account or not with given username
        if ($user_id == null && $error == "") {
            addUser($username, $email, $password, $conn);
            $_SESSION['err_mess'] = "successfully created new account";
            header("Location: sign-in.php");

        } else {
            if($error != "") {
                $_SESSION['err_mess'] = "Username already exists. Choose another username that does not have an account";
                header("Location: sign-up.php");
            }else{
                $_SESSION['err_mess'] = error;
                header("Location: sign-up.php");
            }


        }
    }
}
function validatePassword($field)
{
    $regex = "/[^a-zA-Z0-9-_!$\/%@#]/";

    if ($field == "")
        return "Password is required. ";
    else if (strlen($field)  < 5)
        return "Password must must be at least 5 characters. ";
    else if (preg_match($regex,$field))
        return "Only a-z, A-Z, 0-9, !, $, /, %, @ and # allowed in Password. ";
    return "";
}
function validateName($field)
{
    $regex = "/[^a-zA-Z0-9]_-/";

    if ($field == "")
        return "UserName is required. ";
    else if (strlen($field) < 5)
        return "Username must must be at least 5 characters. ";
    else if (preg_match($regex,$field))
        return "Only a-z, A-Z, 0-9, - and _ allowed in UserName. ";
    return "";
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
    $salt = var_dump(bin2hex(random_bytes(3)));
    $saltier = var_dump(bin2hex(random_bytes(3)));
    $hashPassword = utilities::hashPassword($password, $salt, $saltier);
    $query = "INSERT INTO USER (username,email,password, salty, saltier) VALUES('$username','$email','$hashPassword', '$salt', '$saltier')";
    $result = $conn->query($query);
    if (!$result) utilities::mysql_fatal_error("Cannot add User", $conn);

}


$conn = utilities::databaseCreation($hn,$un,$pw,$db);
register($conn);
$conn->close();


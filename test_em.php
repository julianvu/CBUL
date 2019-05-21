<?php
require_once "utilities.php";
require_once "login.php";
require_once "coordinate.php";

session_start();
$user_id = '';
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];

}
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id();
    $_SESSION['initiated'] = 1;
}

if ($user_id != '' && $_SESSION['check'] == hash('ripemd128', $_SERVER['REMOTE_ADDR'] .
        $_SERVER['HTTP_USER_AGENT'])) {
    $conn = utilities::databaseCreation($hn, $un, $pw, $db);

    echo <<< _END
        <html>
        <head>
        <title>CBUL - Test EM</title>
        </head>
        <body>
            <p>The EM implementation is not functional. Please refer to source code in train_em.php file.</p>
            <p>That file contains the attempted implementation of the EM algorithm. We found it to be too complex to implement, but went as far as we could with it.
            </p>
        </body>
        </html>
_END;
    $conn->close();
} else {
    echo "You are not allowed to access this page without authentication";
}
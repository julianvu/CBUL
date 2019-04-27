<?php
/**
 * Created by Anis Dhapa
 */


/**
 * Check for error message and show Error message to the user
 */
session_start();
$errorMessage = '';
if (isset($_SESSION['err_mess'])) {
    $errorMessage = $_SESSION['err_mess'];
    unset($_SESSION['err_mess']);
}

echo <<<_END


<div class="d-flex justify-content-center align-content-center">
      <form action="register.php" method="post" class="form-sign-up">
        <h2 class="form-signup-heading">SIGN UP PAGE</h2>
        <label for="inputEmail" class="sr-only">Email </label>
        <input type="email" name="inputEmail" id="inputEmail" class="form-control" placeholder="Email address" autofocus>
        <label for="inputUsername" class="sr-only">Username </label>
        <input type="Text" name="inputUsername" id="inputUsername" class="form-control">
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" name="inputPassword" id="inputPassword" class="form-control" placeholder="Password">
        <h4 class="form-signin-heading">$errorMessage</h4>
        <button class="btn btn-lg btn-primary btn-block" type="submit">SIGN UP</button>
      </form>
</div>
_END;

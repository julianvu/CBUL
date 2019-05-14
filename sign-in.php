<?php
/**
 * Created by PhpStorm.
 * User: anisdhapa
 * Date: 2019-04-14
 * Time: 20:55
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


<div class="d-flex justify-content-center align-content-center" >
      <form action="authentication.php" method="post" class="form-signin"  >
        <h2 class="form-signin-heading">Sign In Page</h2>
        <label for="inputUsername" class="sr-only">Username</label>
        <input type="text" name="inputUsername" id="inputUsername" class="form-control" placeholder="Username" autofocus>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" name="inputPassword" id="inputPassword" class="form-control" placeholder="Password">
_END;


echo <<<_END
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
        
        <h4 class="form-signin-heading" id="errorMessage">$errorMessage</h4>
      </form>
      
      <form method="post" action="sign-up.php">
					<button type="submit">SIGN UP PAGE</button>
	    </form>

</div>

_END;

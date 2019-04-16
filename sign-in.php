<?php
/**
 * Created by PhpStorm.
 * User: anisdhapa
 * Date: 2019-04-14
 * Time: 20:55
 */


echo <<<_END


<div class="d-flex justify-content-center align-content-center">
      <form action="upload.php" method="post" class="form-signin" >
        <h2 class="form-signin-heading">Sign In Page</h2>
        <label for="inputEmail" class="sr-only">Email address</label>
        <input type="email" name="inputEmail" id="inputEmail" class="form-control" placeholder="Email address" autofocus>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" name="inputPassword" id="inputPassword" class="form-control" placeholder="Password">
        <div class="checkbox">
          <label>
            <input type="checkbox" name="inputRemember" value="remember-me"> Remember me
          </label>
        </div>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
      </form>
      
      <form method="post" action="sign-up.php">
					<button type="submit">SIGN UP PAGE</button>
	    </form>

</div>
_END;

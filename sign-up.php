<?php
/**
 * Created by PhpStorm.
 * User: anisdhapa
 * Date: 2019-04-15
 * Time: 07:38
 */

echo <<<_END


<div class="d-flex justify-content-center align-content-center">
      <form action="sign-in.php" method="post" class="form-sign-up" onsubmit="return validate(this, '#errorMessage')"
novalidate>
        <h2 class="form-signup-heading">SIGN UP PAGE</h2>
        <label for="inputEmail" class="sr-only">Email address</label>
        <input type="email" name="inputEmail" id="inputEmail" class="form-control" placeholder="Email address" autofocus>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" name="inputPassword" id="inputPassword" class="form-control" placeholder="Password">
      
        <button class="btn btn-lg btn-primary btn-block" type="submit">SIGN UP</button>
      </form>
</div>
_END;

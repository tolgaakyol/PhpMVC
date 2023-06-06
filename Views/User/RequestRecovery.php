<?php

use Tolgaakyol\PhpMVC\Helpers\InputFilter;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=1920, initial-scale=1.0">
  <title>Forgot password</title>
</head>
<body>
  <form method="post">
    <p><input type="email" name="email" placeholder="email"></p>
    <?php
    InputFilter::printErrors($errors ?? null, 'email');
    ?>
    <p><button type="submit">Request recovery e-mail</button></p>
    <a href="/user/login/">Login</a>
    <a href="/user/create/">Register</a>
  </form>
</body>
</html>
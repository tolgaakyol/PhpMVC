<?php

use TolgaAkyol\PhpMVC\Config as Config;
use TolgaAkyol\PhpMVC\Helpers\InputFilter;

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=1920, initial-scale=1.0">
  <title>Register</title>
</head>
<body>
  <form method="post">
    <p><input type="text" name="username" placeholder="username"></p>
    <?php
    InputFilter::printErrors($errors ?? null, 'username');
    ?>
    <p><input type="text" name="email" placeholder="email"></p>
    <?php
    InputFilter::printErrors($errors ?? null, 'email');
    ?>
    <p><input type="password" name="password" placeholder="password"></p>
    <?php
    InputFilter::printErrors($errors ?? null, 'password');
    ?>
    <p><input type="password" name="password_confirm" placeholder="confirm password"></p>
    <?php
    InputFilter::printErrors($errors ?? null, 'password_confirm');
    ?>
    <p><button type="submit">Create</button></p>
    <a href="/user/login/">Log In</a>
  </form>
</body>
</html>
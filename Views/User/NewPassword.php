<?php

use Tolgaakyol\PhpMVC\Helpers\InputFilter;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=1920, initial-scale=1.0">
  <title>Update password</title>
</head>
<body>
  <form method="post">
    <p><input type="email" placeholder="<?php if (isset($email)) { echo $email; } ?>" disabled></p>
    <p><input type="password" name="password" placeholder="new password"></p>
    <?php
    InputFilter::printErrors($errors ?? null, 'password');
    ?>
    <p><input type="password" name="password_confirm" placeholder="confirm new password"></p>
    <?php
    InputFilter::printErrors($errors ?? null, 'password_confirm');
    ?>
    <p><button type="submit">Update</button></p>
    <a href="/user/login/">Login</a>
    <a href="/user/create/">Register</a>
  </form>
</body>
</html>
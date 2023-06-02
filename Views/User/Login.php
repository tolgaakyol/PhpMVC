<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=1920, initial-scale=1.0">
  <title>Login</title>
</head>
<body>
  <form method="post">
    <p><input type="text" name="<?= LOGIN_WITH ?>" placeholder="<?= LOGIN_WITH ?>"></p>
    <p><input type="password" name="password" placeholder="password"></p>
    <p><input type="checkbox" name="remember" value="1">Remember me</p>
    <p><button type="submit">Log In</button></p>
    <a href="/user/recover/">Forgot Password</a>
    <a href="/user/create/">Register</a>
  </form>
</body>
</html>
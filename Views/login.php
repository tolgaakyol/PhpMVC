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
    <input type="text" name="<?= LOGIN_WITH ?>" placeholder="<?= LOGIN_WITH ?>">
    <input type="password" name="password" placeholder="password">
    <input type="checkbox" name="remember">Remember me
    <br />
    <button type="submit">Login</button>
  </form>
</body>
</html>
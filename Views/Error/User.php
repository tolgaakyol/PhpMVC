<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title><?= constant('SITE_NAME') ?> - Error</title>
</head>
<body>
<?php if(isset($message) && isset($caption) && isset($redirectURL) && isset($redirectLabel)): ?>
  <h1><?= $caption ?></h1>
  <p><?= $message ?></p>
  <a href="<?= $redirectURL ?>"><?= $redirectLabel ?></a>
<?php endif; ?>
</body>
</html>
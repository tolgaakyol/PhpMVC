<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title><?= constant('SITE_NAME') ?> - Page not found</title>
</head>
<body>
<h1>Page Not Found</h1>
<p>The page you requested could not be found.</p>
<?php if(isset($message)): ?>
  <p><strong>Error message: </strong><?= $message ?></p>
<?php endif; ?>
<a href="<?= constant('URL_FULL')?>">Home</a>
</body>
</html>
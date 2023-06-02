<?php

ini_set('session.use_strict_mode', true);
session_start();

# Composer autoloader
require 'vendor/autoload.php';

# Load config files
foreach (glob('Config/*.php') as $fileName) {
  include $fileName;
}

foreach (glob(DIR_HELPERS . '*.php') as $fileName) {
  include $fileName;
}

# Error reporting
error_reporting(ERROR_REPORTING);

# System autoloader
spl_autoload_register(function ($class) {
  $class = str_replace('\\', '/', $class);
  include $class . '.php';
});

# Instantiate bootstrap
new System\Bootstrap;
<?php

namespace Tolgaakyol\PhpMVC;

use Tolgaakyol\PhpMVC\Config as Config;

ini_set('session.use_strict_mode', true);
session_start();

# Composer autoloader
require 'vendor/autoload.php';

# Load config files
foreach (glob('Config/*.php') as $fileName) {
  include $fileName;
}

foreach (glob(Config\DIR_HELPERS . '*.php') as $fileName) {
  include $fileName;
}

# Error reporting
error_reporting(Config\ERROR_REPORTING);

# System autoloader
spl_autoload_register(function ($class) {
  $class = str_replace('\\', '/', $class);
  $class = str_replace('Tolgaakyol/PhpMVC/', '', $class);
  include_once $class . '.php';
});

# Instantiate bootstrap
new System\Bootstrap;
<?php

session_start();

# Load config files
foreach (glob('Config/*.php') as $fileName)
{
    include $fileName;
}

foreach (glob(DIR_HELPERS . '*.php') as $fileName)
{
    include $fileName;
}

# Error reporting
error_reporting(ERROR_REPORTING);

# System autoloader
spl_autoload_register(function ($class)
{
    $class = str_replace('\\', '/', $class);
    include $class . '.php';
});

# Instantiate bootstrap
new System\Bootstrap;
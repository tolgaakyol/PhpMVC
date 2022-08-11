<?php

# Load config files
foreach (glob('Config/*.php') as $fileName)
{
    include $fileName;
}

foreach (glob(DIR_HELPERS . '*.php') as $fileName)
{
    include $fileName;
}

# System autoloader
spl_autoload_register(function ($class)
{
    include $class . '.php';
});

# Instantiate bootstrap
new System\Bootstrap;
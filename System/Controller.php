<?php

namespace TolgaAkyol\PhpMVC\System;

use TolgaAkyol\PhpMVC\Config as Config;

class Controller
{
    public function model($modelName):mixed {
        $fileName = Config\DIR_MODELS . $modelName . '.php';
        $instance = ucwords(Config\DIR_MODELS) . $modelName;
        $instance = str_replace('/', '\\', $instance);
        $instance = Config\PACKAGE_PREFIX . $instance;

        if (file_exists($fileName))
        {
            include $fileName;
            return new $instance;    
        } else {
            exit('Model file not found'); // ERRMSG
        }
    }

    public function view($viewName, $content = null): void {
        if (!empty($content) && is_array($content)) { extract($content); }
        include Config\DIR_VIEWS . $viewName . '.php';
    }
}
<?php

namespace System;

class Controller
{
    public function model($modelName)
    {
        $fileName = DIR_MODELS . $modelName . '.php';
        $instance = ucwords(DIR_MODELS) . $modelName;
        $instance = str_replace('/', '\\', $instance);

        if (file_exists($fileName))
        {
            include $fileName;
            return new $instance;    
        } else {
            exit('Model file not found'); // ERRMSG
        }
    }

    public function view($viewName, $content = null)
    {
        if (!empty($content) && is_array($content)) { extract($content); }
        include DIR_VIEWS . $viewName . '.php';
    }
}
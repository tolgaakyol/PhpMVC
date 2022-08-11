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
            exit('Model file not found');    // TODO - file not found
        }
    }

    public function view($viewName)
    {
        include DIR_VIEWS . $viewName . '.php';
    }
}
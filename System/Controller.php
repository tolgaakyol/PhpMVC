<?php

namespace TolgaAkyol\PhpMVC\System;

use TolgaAkyol\PhpMVC\Application;
use TolgaAkyol\PhpMVC\Config as Config;

class Controller
{
  public function model($modelName, bool $isCore = false): mixed
  {
    $prefix = $isCore ? Application::$PATH_CORE : Application::$PATH_EXT;
    $fileName = $prefix . Config\DIR_MODELS . $modelName . '.php';
    $instance = ucwords(Config\DIR_MODELS) . $modelName;
    $instance = str_replace('/', '\\', $instance);
    $instance = Config\PACKAGE_PREFIX . $instance;

    if (file_exists($fileName)) {
      include $fileName;
      return new $instance;
    } else {
      exit('Model file not found'); // ERRMSG
    }
  }

  public function view($viewName, $content = null, bool $isCore = false): void
  {
    if (!empty($content) && is_array($content)) {
      extract($content);
    }

    $prefix = $isCore ? Application::$PATH_CORE : Application::$PATH_EXT;
    $path = $prefix . Config\DIR_VIEWS . $viewName . '.php';

    if(file_exists($path)) {
      include $prefix . Config\DIR_VIEWS . $viewName . '.php';
    } else {
      die('View file not found!'); // ERRMSG
    }
  }
}
<?php

namespace TolgaAkyol\PhpMVC\System;

use TolgaAkyol\PhpMVC\Application;

class Controller
{
  public function model($modelName, bool $isCore = false): mixed
  {
    $prefix = $isCore ? Application::$PATH_CORE : Application::$PATH_EXT;
    $fileName = $prefix . DIR_MODELS . $modelName . '.php';
    $instance = ucwords(DIR_MODELS) . $modelName;
    $instance = str_replace('/', '\\', $instance);
    $instance = PACKAGE_PREFIX . $instance;

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
    $path = $prefix . DIR_VIEWS . $viewName . '.php';

    if(file_exists($path)) {
      include $prefix . DIR_VIEWS . $viewName . '.php';
    } else {
      die('View file not found!'); // ERRMSG
    }
  }
}
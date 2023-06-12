<?php

namespace TolgaAkyol\PhpMVC\System;

use Exception;
use TolgaAkyol\PhpMVC\Application;

class Bootstrap
{
  private string $controller;
  private string $method;
  private array $args = [];

  public function __construct()
  {
    try {
      $this->controller = constant('INDEX_CONTROLLER');
      $this->method = constant('INDEX_METHOD');
    } catch (Exception $e) {
      Log::toFile(LogType::Critical, __METHOD__, 'Constant not defined. ' . $e->getMessage());
      die('Unable to proceed with the request due to system error.');
    }

    $this->parseURL();
    $this->initialize();
  }

  private function parseURL(): void
  {
    $url = filter_input(INPUT_SERVER, 'REQUEST_URI');
    $url = trim((string)$url, '/');
    $url = explode('/', $url);

    if (!empty($url[0])) {
      $this->controller = ucwords($url[0]);
    }
    if (!empty($url[1])) {
      $this->method = $url[1];
    }

    if (!empty($url[0]) && !empty($url[1]) && !empty($url[2])) {
      $this->args = array_slice($url, 2);
    }
  }

  private function initialize(): void
  {
    try {
      $isCore = true;
      if($this->controller == 'Favicon.ico')  { return; }
      $controller = constant('DIR_CONTROLLERS') . $this->controller;
      if (file_exists(Application::$PATH_CORE . $controller . '.php')) {
        include Application::$PATH_CORE . $controller . '.php';
      } else if (file_exists(Application::$PATH_EXT . $controller . '.php')) {
        $isCore = false;
        include Application::$PATH_EXT . $controller . '.php';
      } else {
        throw new Exception('Controller not found!: ' . $this->controller);
      }
      $controller = str_replace('/', '\\', $controller);
      $controller = $isCore ? constant('PACKAGE_PREFIX') . $controller : Application::$PROJECT_PREFIX . $controller;
      $instance = new $controller;
      $handler = [$instance, $this->method];

      if (isset($this->method) && is_callable($handler)) {
        call_user_func_array($handler, $this->args);
      } else {
        throw new Exception('Unable to call method: ' . $this->method);
      }
    } catch (Exception $e) {
      Log::toFile(LogType::Critical, __METHOD__, 'Unable to initialize: ' . $e->getMessage());
      die('Unable to initialize');
    }
  }
}
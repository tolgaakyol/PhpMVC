<?php

namespace TolgaAkyol\PhpMVC\System;

use TolgaAkyol\PhpMVC\Application;

class Bootstrap
{
    private string $controller = (string) INDEX_CONTROLLER;
    private string $method = (string) INDEX_METHOD;
    private array $args = [];

    public function __construct()
    {
        $this->parseURL();    
        $this->initialize();    
    }

    private function parseURL(): void
    {
        $url    = filter_input(INPUT_SERVER, 'REQUEST_URI');         
        $url    = trim((string) $url, '/');
        $url    = explode('/', $url);

        if (!empty($url[0])) { $this->controller = ucwords($url[0]); }
        if (!empty($url[1])) { $this->method = $url[1]; }

        if (!empty($url[0]) && !empty($url[1]) && !empty($url[2]))
        {
            $this->args = array_slice($url, 2);
        }
    }

    private function initialize(): void
    {
		$isCore = true;
        $controller = DIR_CONTROLLERS . $this->controller;
        if(file_exists(Application::$PATH_CORE . $controller . '.php')) {
          include Application::$PATH_CORE . $controller . '.php';
        } else if (file_exists(Application::$PATH_EXT . $controller . '.php')) {
		      $isCore = false;
          include Application::$PATH_EXT . $controller . '.php';
        } else {
          die('Controller not found!');
        }
        $controller = str_replace('/', '\\', $controller);
        $controller = $isCore ? PACKAGE_PREFIX . $controller : Application::$PROJECT_PREFIX . $controller;
        $instance = new $controller;
        $handler = [$instance, $this->method];

        if (isset($this->method) && is_callable($handler))
        {
            call_user_func_array($handler, $this->args);
        } else {
            Log::toFile(LogType::Critical, __METHOD__, "Unable to call method: " . $this->method);
            exit('Unable to call method: ' . $this->method); // ERRMSG
        }
    }
}
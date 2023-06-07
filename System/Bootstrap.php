<?php

namespace TolgaAkyol\PhpMVC\System;

use TolgaAkyol\PhpMVC\Application;
use TolgaAkyol\PhpMVC\Config as Config;

class Bootstrap
{
    private string $controller = Config\DEFAULT_CONTROLLER;
    private string $method = Config\DEFAULT_METHOD;
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
        $controller = Config\DIR_CONTROLLERS . $this->controller;
        if(file_exists(Application::$PATH_CORE . $controller . '.php')) {
          include Application::$PATH_CORE . $controller . '.php';
        } else {
		  $isCore = false;
          include Application::$PATH_EXT . $controller . '.php';
        }
        $controller = str_replace('/', '\\', $controller);
        $controller = $isCore ? Config\PACKAGE_PREFIX . $controller : Application::$PROJECT_PREFIX . $controller;
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
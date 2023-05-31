<?php

namespace System;

class Bootstrap
{

    private string $controller = DEFAULT_CONTROLLER;
    private string $method = DEFAULT_METHOD;
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
        $controller = DIR_CONTROLLERS . $this->controller;
        include $controller . '.php';
        $controller = str_replace('/', '\\', $controller);
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
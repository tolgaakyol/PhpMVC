<?php

namespace TolgaAkyol\PhpMVC\System;

use Exception;
use TolgaAkyol\PhpMVC\Application;

class Controller
{
  public function model($modelName, bool $isCore = false): mixed
  {
    try {
      $prefix = $isCore ? Application::$PATH_CORE : Application::$PATH_EXT;
      $fileName = $prefix . constant('DIR_MODELS') . $modelName . '.php';
      $instance = ucwords(constant('DIR_MODELS')) . $modelName;
      $instance = str_replace(DIRECTORY_SEPARATOR, '\\', $instance);
      $instance = $isCore ? constant('PACKAGE_PREFIX') . $instance : Application::$PROJECT_PREFIX . $instance;

      if (file_exists($fileName)) {
        include $fileName;
        return new $instance;
      } else {
        throw new Exception("Model file ($fileName) not found");
      }
    } catch (Exception $e) {
      Log::toFile(LogType::Critical, __METHOD__, 'Unable to load model: ' . $e->getMessage());
      Controller::systemError($e->getMessage(), __METHOD__);
      die();
    }
  }

  public static function view($viewName, $content = null, bool $isCore = false): void
  {
    if (!empty($content) && is_array($content)) {
      extract($content);
    }

    try {
      $prefix = $isCore ? Application::$PATH_CORE : Application::$PATH_EXT;
      $path = $prefix . constant('DIR_VIEWS') . $viewName . '.php';

      if(file_exists($path)) {
        include $prefix . constant('DIR_VIEWS') . $viewName . '.php';
      } else {
        throw new Exception("View file ($viewName) not found");
      }
    } catch (Exception $e) {
      Log::toFile(LogType::Critical, __METHOD__, 'Unable to load view: ' . $e->getMessage());
      die('Unable to proceed with the request due to system error.');
    }
  }

  public static function displayError($page = null, $arguments = [], $httpResponseCode = null): void {
    if(is_null($page)) {
      $view = 'Error/404';
      http_response_code(404);
    } else {
      $view = $page;
    }

    if(!is_null($httpResponseCode)) {
      http_response_code($httpResponseCode);
    }

    self::view($view, $arguments, constant('USE_CORE_VIEWS'));
    die();
  }

  public static function systemError($message = '', $method = ''): void {
    if(Application::$frameworkDevMode) {
      $messageToDisplay = $method . ' >> ' . $message;
    } else {
      $messageToDisplay = 'The system encountered an error. Please contact the administrator.';
    }

    self::displayError('Error/System', [
        'pageTitle' => 'System error',
        'caption' => 'System error',
        'message' => $messageToDisplay
    ], 500);
  }

  public function getScript($script): string {
    return str_replace('\\', '/', '//' . constant('URL_ROOT') . '/' . constant('DIR_PUBLIC') . 'js/' . $script . '.js');
  }
}
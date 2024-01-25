<?php

namespace TolgaAkyol\PhpMVC\Helpers;

use TolgaAkyol\PhpMVC\Application;
use TolgaAkyol\PhpMVC\System\FileHandler;

class ErrorUnit {
  public string $caption;
  public string $message;
  public string $targetView;
  public string $redirectURL;
  public string $redirectLabel;
  public int $httpResponseCode;

  public function __construct(ErrorType $errorType) {
    $errorContent = $this->getInfoForErrorType($errorType);
    $this->caption = $errorContent['caption'];
    $this->message = $errorContent['message'];
    $this->targetView = $errorContent['targetView'];
    $this->redirectURL = $errorContent['redirectURL'];
    $this->redirectLabel = $errorContent['redirectLabel'];
    $this->httpResponseCode = $errorContent['httpResponseCode'];
  }

  public function getInfoForErrorType(ErrorType $errorType): array {
    $prefix = constant('USE_CORE_VIEWS') ? Application::$PATH_CORE : Application::$PATH_EXT;
    $path = $prefix . constant('DIR_VIEWS') . 'Error/';
    $fileHandler = new FileHandler($path);
    $errorList = $fileHandler->getJSONFileAsArray('errors.json');

    foreach ($errorList as $error) {
      if ($error['id'] === $errorType->value) {
        return [
            'caption' => $error['caption'],
            'message' => $error['message'],
            'targetView' => $error['targetView'],
            'redirectURL' => $error['redirectURL'],
            'redirectLabel' => $error['redirectLabel'],
            'httpResponseCode' => $error['httpResponseCode'],
        ];
      }
    }

    return [
        'caption' => 'Unknown error',
        'message' => 'An unknown error has occurred.',
        'targetView' => false,
        'redirectURL' => false,
        'redirectLabel' => false,
        'httpResponseCode' => 500,
    ];
  }
}
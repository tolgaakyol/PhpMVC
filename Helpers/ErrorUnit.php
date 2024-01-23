<?php

namespace TolgaAkyol\PhpMVC\Helpers;

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
    // TODO: Read the json or txt file that includes text for the given error type and set the properties of the ErrorUnit object accordingly
    return [
      'caption' => 'Error',
      'message' => 'An error occurred.',
      'targetView' => 'create',
      'redirectURL' => '/',
      'redirectLabel' => 'Go to Home Page',
      'httpResponseCode' => 400,
    ];
  }
}
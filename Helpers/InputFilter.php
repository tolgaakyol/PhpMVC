<?php

// TODO: definition & info

namespace Helpers;

class InputFilter {
  private InputFilterField $currentField;

  private array $errors = [];
  private array $values = [];

  public function __construct() {
  }

  // TODO: Add an argument to methods that blocks producing errors (~ hides them)

  public function get($name): static {
    $this->currentField = new InputFilterField($name, INPUT_GET);

    isset($this->currentField->value) && $this->values[$name] = $this->currentField->value;
    return $this;
  }

  public function post($name): static {
    $this->currentField = new InputFilterField($name, INPUT_POST);

    isset($this->currentField->value) && $this->values[$name] = $this->currentField->value;
    return $this;
  }

  // TODO: Test & improve cookie filter
  /*public function cookie($name, $key): static {
    $this->currentField = new InputFilterField($name, INPUT_COOKIE, $key);

    isset($this->currentField->value) && $this->values[$name] = $this->currentField->value;
    return $this;
  }*/

  public function required(): static {
    if(empty($this->currentField->value)) {
      $errorType = InputFilterError::RequiredFieldEmpty;
      $this->errors[$this->currentField->name][] = ['type' => $errorType, 'message' => $this->getErrorMessage($errorType)];
    }

    return $this;
  }

  public function email(): static {
    if(!filter_var($this->currentField->value, FILTER_VALIDATE_EMAIL)) {
      $errorType = InputFilterError::InvalidEmail;
      $this->errors[$this->currentField->name][] = ['type' => $errorType, 'message' => $this->getErrorMessage($errorType)];
    }

    return $this;
  }

  public function length(int $min, int|false $max = false): static {
    if(gettype($this->currentField->value) != 'string') {
      $errorType = InputFilterError::InvalidType;
      $this->errors[$this->currentField->name][] = ['type' => $errorType, 'message' => $this->getErrorMessage($errorType)];
    } else if (!$max && strlen($this->currentField->value) != $min) {
      $errorType = InputFilterError::InvalidLength;
      $limits = [$min];
      $this->errors[$this->currentField->name][] = ['type' => $errorType, 'message' => $this->getErrorMessage($errorType, $limits)];
    } else if (strlen($this->currentField->value) < $min || strlen($this->currentField->value) > $max) {
      $errorType = InputFilterError::InvalidLength;
      $limits = [$min, $max];
      $this->errors[$this->currentField->name][] = ['type' => $errorType, 'message' => $this->getErrorMessage($errorType, $limits)];
    }

    return $this;
  }

  public function range(int $min, int $max): static {
    if(!is_numeric($this->currentField->value)) {
      $errorType = InputFilterError::InvalidType;
      $this->errors[$this->currentField->name][] = ['type' => $errorType, 'message' => $this->getErrorMessage($errorType)];
    } else if ($this->currentField->value < $min || $this->currentField->value > $max) {
      $errorType = InputFilterError::OutOfRange;
      $limits = [$min, $max];
      $this->errors[$this->currentField->name][] = ['type' => $errorType, 'message' => $this->getErrorMessage($errorType, $limits)];
    }

    return $this;
  }

  public function numbersOnly(): static {
    if(!ctype_digit($this->currentField->value)) {
      $errorType = InputFilterError::NotNumeric;
      $this->errors[$this->currentField->name][] = ['type' => $errorType, 'message' => $this->getErrorMessage($errorType)];
    }

    return $this;
  }

  public function lettersOnly(): static {
    if(!ctype_alpha($this->currentField->value)) {
      $errorType = InputFilterError::NotAlphabetic;
      $this->errors[$this->currentField->name][] = ['type' => $errorType, 'message' => $this->getErrorMessage($errorType)];
    }

    return $this;
  }

  public function alphanumeric(): static {
    if(!ctype_alnum($this->currentField->value)) {
      $errorType = InputFilterError::NotAlphanumeric;
      $this->errors[$this->currentField->name][] = ['type' => $errorType, 'message' => $this->getErrorMessage($errorType)];
    }

    return $this;
  }

  public function customFilter($regex): static {
    if(!preg_match($regex, $this->currentField->value)) {
      $errorType = InputFilterError::InvalidFormat;
      $this->errors[$this->currentField->name][] = ['type' => $errorType, 'message' => $this->getErrorMessage($errorType)];
    }

    return $this;
  }

  public function equalTo($fieldName): static {
    if(!isset($this->values[$fieldName]) || $this->currentField->value != $this->values[$fieldName]) {
      $errorType = InputFilterError::NotEqual;
      $this->errors[$this->currentField->name][] = ['type' => $errorType, 'message' => $this->getErrorMessage($errorType)];
    }

    return $this;
  }

  public function getErrors(): array|false {
    if(empty($this->errors)) {
      return false;
    } else {
      $this->checkRequiredError();

      return $this->errors;
    }
  }

  // TODO: Add an argument to allow styling, etc.
  public static function printErrors(array|null $errorContainer, string $fieldName): void {
    if(is_null($errorContainer)) {
      return;
    }

    if(!empty($errorContainer[$fieldName])) {
      foreach($errorContainer[$fieldName] as $errors) {
        echo '<p>' . $errors['message'] . '</p>';
      }
    }
  }

  public function getValues(): array {
    return $this->values;
  }

  // TODO: Add functionality to allow custom error sets (possibly in json format and should allow rules like $min, $max to be included in messages)
  private function getErrorMessage(InputFilterError $type, array|null $limits = null): string {
    switch ($type) {
      case InputFilterError::RequiredFieldEmpty:
        return "Required field left empty.";
      case InputFilterError::InvalidEmail:
        return "Invalid e-mail address.";
      case InputFilterError::InvalidType:
        return "Invalid type.";
      case InputFilterError::InvalidLength:
        return "Invalid length.";
      case InputFilterError::OutOfRange:
        return "Out of range.";
      case InputFilterError::NotNumeric:
        return "Not entirely numeric.";
      case InputFilterError::NotAlphabetic:
        return "Not entirely alphabetic.";
      case InputFilterError::NotAlphanumeric:
        return "Not entirely alphanumeric.";
      case InputFilterError::InvalidFormat:
        return "Invalid format.";
      case InputFilterError::NotEqual:
        return "Fields do not match";
    }

    return "Invalid";
  }

  private function checkRequiredError(): void {
    if(!empty($this->errors)) {
      foreach($this->errors as $fieldName => $errors) {
        foreach($errors as $key => $error) {
          if(in_array(InputFilterError::RequiredFieldEmpty, $error)) {
            $this->errors[$fieldName] = [$error];
          }
        }
      }
    }
  }
}

enum InputFilterError {
  case RequiredFieldEmpty;
  case InvalidEmail;
  case InvalidType;
  case InvalidLength;
  case OutOfRange;
  case NotNumeric;
  case NotAlphabetic;
  case NotAlphanumeric;
  case InvalidFormat;
  case NotEqual;
}

class InputFilterField {

  public string $name;
  public int|string|bool $value;

  public function __construct($name, int $method, string $cookieKey = null) {
    switch ($method) {
      case INPUT_GET:
        $this->name = $name;
        $this->value = isset($_GET[$name]) ? htmlspecialchars($_GET[$name]) : '';
        break;
      case INPUT_POST:
        $this->name = $name;
        $this->value = isset($_POST[$name]) ? htmlspecialchars($_POST[$name]) : '';
        break;
      case INPUT_COOKIE:
        $this->name = $name;
        if (!is_null($cookieKey) && isset($_COOKIE[$name][$cookieKey])) {
          $this->value = htmlspecialchars($_COOKIE[$name][$cookieKey]);
        } else {
          $this->value = '';
        }
        break;
    }
  }
}
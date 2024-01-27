<?php

namespace TolgaAkyol\PhpMVC\Models;

use Error;
use TolgaAkyol\PhpMVC\Config\TokenUseCase;
use TolgaAkyol\PhpMVC\System\{Controller, Model, Log, LogType};
use TolgaAkyol\PhpMVC\Helpers\SQLFilter;

class User extends Model
{
  public function __construct() {
    parent::__construct();
  }

  public function list() // TEST
  {
    return $this->select('users', '*');
  }

  public function login($userData): array|false {
    [$login, $password] = $userData;

    try {
      if ($this->checkIfExists(constant('LOGIN_WITH'), $login, true)) {
        $storedUserData = $this->getUserByKey(constant('LOGIN_WITH'), $login);

        if (password_verify($password, $storedUserData['password'])) {
          if(isset($storedUserData['password'])) { unset($storedUserData['password']); }
          return $storedUserData;
        } else {
          return false;
        }
      } else {
        return false;
      }
    } catch (Error $e) {
      Log::toFile(LogType::Critical, __METHOD__, $e->getMessage());
      Controller::systemError(__METHOD__, $e->getMessage());
      return false;
    }
  }

  public function create($userData): bool {
    [$userId, $username, $password, $email, $level] = $userData;

    $content = array(
        "user_id" => $userId,
        "username" => $username,
        "password" => $password,
        "email" => $email,
        "level" => $level);

    return $this->insert("users", $content);
  }

  public function getUserByKey($key, $value): array|false {
    // FIXME: Might be error-prone if $result returns empty
    $where = new SQLFilter($key, "=", $value);
    $result = $this->select("users", "*", $where->getStmt(), $where->getValues());
    return $result ? $result[0] : false;
  }

  public function getUserIdByKey($key, $value): string|false {
    $where = new SQLFilter($key, '=', $value);

    $result = $this->select('users', 'user_id', $where->getStmt(), $where->getValues());

    return $result ? $result[0]['user_id'] : false;
  }

  public function checkIfExists(string $field, string $value, bool $logWarning = false, string $table = 'users'): bool {
    $where = new SQLFilter($field, "=", $value);

    $result = $this->select($table, $field, $where->getStmt(), $where->getValues());

    if ($logWarning && count($result) > 1) {
      Log::toFile(LogType::Critical, __METHOD__, "Duplicate $field!: $value");
      return true;
    }

    if (count($result) == 1) {
      return true;
    } else {
      return false;
    }
  }

  public function activateUser($userId): bool {
    $where = new SQLFilter('user_id', '=', $userId);
    $result = $this->update('users', ['level' => '1'], $where->getStmt(), $where->getValues());

    if($result) {
      $where->and('use_case', '=', TokenUseCase::Activation->value);
      $result = $this->delete('tokens', $where->getStmt(), $where->getValues());
    }

    return $result;
  }

  public function updatePassword(string $userId, string $password): bool {
    $where = new SQLFilter('user_id', '=', $userId);
    return $this->update('users', ['password' => $password], $where->getStmt(), $where->getValues());
  }

  public function storeToken($content): bool {
    return $this->insert('tokens', $content);
  }

  public function getToken($token, $useCase): false|array {
    $where = new SQLFilter('token', '=', $token);
    $where->and('use_case', '=', $useCase);

    $result = $this->select('tokens', '*', $where->getStmt(), $where->getValues());

    return $result ? $result[0] : false;
  }

  public function avoidDuplicateToken(string $userId, int $useCase): void {
    $where = new SQLFilter('user_id', '=', $userId);
    $where->and('use_case', '=', $useCase);

    $this->delete('tokens', $where->getStmt(), $where->getValues(), 0);
  }

  public function destroyToken(string $token): bool {
    $where = new SQLFilter('token', '=', $token);

    return $this->delete('tokens', $where->getStmt(), $where->getValues());
  }
}
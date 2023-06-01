<?php

namespace Models;

use System\Model;
use System\Log, System\LogType;
use Helpers\SQLFilter;

class User extends Model
{
  public function __construct()
  {
    parent::__construct();
  }

  public function list() // TEST
  {
    return $this->select('users', '*');
  }

  public function login($userData) // TEST
  {
    [$login, $password] = $userData;

    if ($this->checkIfExists(LOGIN_WITH, $login, true)) {
      $storedUserData = $this->getUser($login);

      if (password_verify($password, $storedUserData['password'])) {
        unset($storedUserData['password']);
        return $storedUserData;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  public function create($userData): bool
  {
    [$userId, $username, $password, $email, $level] = $userData;

    $content = array(
        "user_id" => $userId,
        "username" => $username,
        "password" => $password,
        "email" => $email,
        "level" => $level);

    return $this->insert("users", $content);
  }

  public function getUser(string $login)
  {
    // FIXME: Might be error-prone if $result returns empty
    $where = new SQLFilter(LOGIN_WITH, "=", $login);
    $result = $this->select("users", "*", $where->getStmt(), $where->getValues());
    return $result[0];
  }

  public function checkIfExists(string $field, string $value, bool $logWarning = false, string $table = 'users'): bool
  {
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

  public function storeNonce($content): bool {
    return $this->insert('nonces', $content);
  }

  public function getNonce($token, $useCase) {
    $where = new SQLFilter('token', '=', $token);
    $where->and('use_case', '=', $useCase);

    $result = $this->select('nonces', '*', $where->getStmt(), $where->getValues());

    if(!$result) { return false; }

    return $result[0];
  }

  public function activateUser($userId): bool {
    $where = new SQLFilter('user_id', '=', $userId);
    $result = $this->update('users', ['level' => '1'], $where->getStmt(), $where->getValues());

    if($result) {
      $where->and('use_case', '=', \NonceUseCase::Activation->value);
      $result = $this->delete('nonces', $where->getStmt(), $where->getValues());
    }

    return $result;
  }
}
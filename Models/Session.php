<?php

namespace TolgaAkyol\PhpMVC\Models;

use TolgaAkyol\PhpMVC\System\Controller;
use TolgaAkyol\PhpMVC\System\Model;
use TolgaAkyol\PhpMVC\Helpers\SQLFilter;

class Session extends Model {

  public const session = 'session';
  public const cookie = 'cookie';
  public const name = 'username';
  public const id = 'user_id';

  public function __construct()
  {
    parent::__construct();
  }

  public function storeSessionToken($sessionData): bool {
    $where = new SQLFilter('session_id', '=', $sessionData['session_id']);
    $where->and('user_id', '=', $sessionData['user_id']);

    if(count($this->select('sessions', 'session_id', $where->getStmt(), $where->getValues())) > 0) {
      return true;
    }

    return $this->insert("sessions", $sessionData);
  }

  public function storeAuthCookie($cookieData): bool{
    return $this->insert("cookies", $cookieData);
  }

  public function getUserByKey(string $key, string $value) {
    $where = new SQLFilter($key, "=", $value);
    $result = $this->select("users", "*", $where->getStmt(), $where->getValues());
    return $result[0];
  }

  public function getStoredEntry(string $type, string $value): mixed {
    switch ($type) {
      case self::session:
        $table = 'sessions';
        $key = 'session_id';
        break;
      case self::cookie:
        $table = 'cookies';
        $key = 'secret';
        break;
      default:
        Controller::systemError('Incorrect arguments.', __METHOD__);
    }

    $where = new SQLFilter($key, "=", $value);
    $result = $this->select($table, "*", $where->getStmt(), $where->getValues());

    return $result[0] ?? false;
  }

  public function countUserSessions(string $userId): int {
    $where = new SQLFilter('user_id', '=', $userId);

    $result = $this->select('sessions', 'user_id', $where->getStmt(), $where->getValues());

    if($result) {
      return count($result);
    } else {
      return 0;
    }
  }

  public function destroyUserSession(string $userId): void {
    $where = new SQLFilter('user_id', '=', $userId);
    $result = $this->select('sessions', 'session_id', $where->getStmt(), $where->getValues(), null, 'created_at');
    unset($where);

    if(!empty($result)) {
      $where = new SQLFilter('session_id', '=', $result[0]['session_id']);
      $this->delete('sessions', $where->getStmt(), $where->getValues());
      $this->delete('cookies', $where->getStmt(), $where->getValues());
    }
  }

  public function logout(string|false $userId = false): void {
    $where = new SQLFilter("session_id", "=", session_id());

    if ($userId) {
      $where->or('user_id', '=', $userId);
    }

    $this->delete('sessions', $where->getStmt(), $where->getValues(), 0);
    $this->delete('cookies', $where->getStmt(), $where->getValues(), 0);
  }
}
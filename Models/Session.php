<?php

namespace Models;

use System\Model;
use Helpers\SQLFilter;

class Session extends Model {

  public const session = 'sessions';
  public const cookie = 'cookies';
  public const name = 'username';
  public const id = 'user_id';

  public function __construct()
  {
    parent::__construct();
  }

  public function storeSessionToken($sessionData): bool {
    // FIXME: Entries that are no longer of use are likely to pile up in the database
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

  public function getStoredEntryByToken($type, $token): mixed {
    $where = new SQLFilter("token", "=", $token);
    $result = $this->select($type, "*", $where->getStmt(), $where->getValues());

    return $result[0] ?? false;
  }

  public function logout($token): void {
    $where = new SQLFilter("token", "=", $token);
    $this->delete("sessions", $where->getStmt(), $where->getValues());
  }
}
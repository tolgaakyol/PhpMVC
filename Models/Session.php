<?php

namespace Models;

use System\Model;
use Helpers\SQLFilter;

class Session extends Model {
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

  public function getUser($username) {
    $where = new SQLFilter("username", "=", $username);
    $result = $this->select("users", "*", $where->getStmt(), $where->getValues());
    return $result[0];
  }

  public function getStoredSession($token) {
    $where = new SQLFilter("token", "=", $token);
    $result = $this->select("sessions", "*", $where->getStmt(), $where->getValues());

    return $result[0];
  }

  public function logout($token): void {
    $where = new SQLFilter("token", "=", $token);
    $this->delete("sessions", $where->getStmt(), $where->getValues());
  }
}
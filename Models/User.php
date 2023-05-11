<?php

namespace Models;

use System\Model;
use System\Log, System\LogType;
use Helpers\SQLFilter;

enum UserLevels: int {
    case Inactive = 0;
    case Standard = 1;
    case Admin = 2;
}

class User extends Model {
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
        return $this->validateCredentials($userData);
    }

    public function create($userData) {
        [$userId, $username, $password, $email] = $userData;

        $content = array(
            "user_id"   => $userId,
            "username"  => $username,
            "password"  => password_hash($password, PASSWORD_DEFAULT),
            "email"     => $email,
            "level"     => UserLevels::Inactive->value);

        return $this->insert("users", $content);
    }

    public function validateCredentials($userData) {
        [$username, $password] = $userData;

        if ($this->checkIfExists("username", $username, true)) {            
            $storedUserData = $this->getUser($username);

            return password_verify($password, $storedUserData['password']) ? true : false;
        } else {
            return false;
        }
    }

    public  function getUser($username) {
        $where = new SQLFilter("username", "=", $username);
        $result = $this->select("users", "*", $where->getStmt(), $where->getValues());
        return $result[0];
    }

    public function checkIfExists($field, $value, $logWarning = false) {
        $where = new SQLFilter($field, "=", $value);

        $result = $this->select("users", $field, $where->getStmt(), $where->getValues());

        if ($logWarning && count($result) > 1) {
            Log::toFile(LogType::Critical, __METHOD__, "Duplicate $field!: $value");
            return false;
        }

        if (count($result) == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function checkPermission() {

    }
}
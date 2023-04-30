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

    public function test() // TEST
    {
        $result = $this->select('users', '*');

        echo "<pre>";
        print_r($result);
        echo "</pre>";
    }

    public function login($userData) // TEST
    {
        return $this->validateCredentials($userData);
    }

    public function create($userData) {
        [$username, $password, $email] = $userData;

        $content = array(
            "username"  => $username,
            "password"  => password_hash($password, PASSWORD_DEFAULT),
            "email"     => $email,
            "level"     => UserLevels::Inactive->value);

        return $this->insert("users", $content);
    }

    public function validateCredentials($userData) {
        [$username, $password] = $userData;

        if ($this->checkIfUserExists($username)) {            
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

    public function checkIfUserExists($username) {
        $where = new SQLFilter("username", "=", $username);

        $result = $this->select("users", "username", $where->getStmt(), $where->getValues());
        if (count($result) == 1){
            return true;
        } elseif (count($result) > 1) {
            Log::toFile(LogType::Critical, __METHOD__, "Duplicate username!: $username");
            return false;
        } else {
            return false;
        }
    }

    public function checkPermission() {

    }
}
<?php

namespace Models;

use System\Model;
use Helpers\SQLWhere;

class User extends Model {
    public function __construct()
    {
        parent::__construct();
    }

    public function test() // TEST
    {
        $where = new SQLWhere("password", "=", "123123");
        $where->and("username", "=", "tolgaakyol")->and("username", "<>", "asd")->andnot("user_id", ">", "1");
        $result = $this->select('users', '*', $where->stmt(), $where->values(), null, 'user_id ASC');

        echo "<pre>";
        print_r($result);
        echo "</pre>";
    }
}
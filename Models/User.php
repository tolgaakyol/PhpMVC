<?php

namespace Models;
use System\Model;
use System\SqlWhere;

class User extends Model {
    public function __construct()
    {
        parent::__construct();
        $this->test();
    }

    public function test()
    {
        $where = new SqlWhere("password", "=", "123123");
        $where->and("username", "=", "tolgaakyol");
        $result = $this->select('users', '*', $where->get(), null, 'user_id ASC');
        
        echo "<pre>";
        print_r ($result);
        echo "</pre>";
    }
}
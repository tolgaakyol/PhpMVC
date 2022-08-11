<?php

namespace Models;
use System\Model;

class User extends Model {
    public function __construct()
    {
        parent::__construct();
        $this->test();
    }

    public function test()
    {
        $where = $this->where('user_id');
        $values = ['user_id' => '2'];
        $result = $this->select('users', '*', null, null, 'user_id ASC');
        
        echo "<pre>";
        print_r ($result);
        echo "</pre>";
    }
}
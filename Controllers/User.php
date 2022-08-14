<?php

namespace Controllers;
use System\Controller;

class User extends Controller {
    private $model;
    
    public function __construct()
    {
        $this->test();
    }

    public function test()
    {
        $this->model('User');       
    }
}
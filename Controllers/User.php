<?php

namespace Controllers;
use System\Controller;

class User extends Controller {
    private $model;
    
    public function __construct()
    {
        
    }

    public function testModel()
    {
        $this->model = $this->model('User');
       
    }
}
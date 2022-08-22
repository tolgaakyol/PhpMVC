<?php

namespace Controllers;

use System\Controller;

class User extends Controller {    
    private \Models\User $model;
    
    public function __construct()
    {
        $this->model = $this->model('User');
    }

    public function home()
    {
        $this->model->test(); // TEST
    }
}
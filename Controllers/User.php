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

    public function login() // TEST
    {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $result = $this->model->login([$username, $password]);

        echo $result ? "Authorized" : "Unauthorized";
    }

    public function create() // TEST
    {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];
        $email = $_POST['email'];

        if(!isset($username) || !isset($password_confirm) || !isset($password) || !isset($email)){
            die("Missing fields"); // ERRMSG
        }

        if($password != $password_confirm){
            die("Passwords do not match"); // ERRMSG
        }
        
        if($this->model->checkIfUserExists($username)) {
            die("Username already exists"); // ERRMSG
        }

        $this->model->create([$username, $password, $email]) ? print("User created") : die("Error"); // ERRMSG

    }
}
<?php

namespace Controllers;

use System\Controller;
use System\Session;

class User extends Controller {    
    private \Models\User $model;
    private $session;
    
    public function __construct()
    {
        $this->session = Session::checkUserSession();
        $this->model = $this->model('User');
    }

    public function home() // TEST
    {
        echo "<pre>";
        print_r($this->model->list());
        echo "</pre>";
    }

    public function login() // TEST
    {   
        if($this->session != null){
            $this->profile();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if(!isset($_POST['username']) || !isset($_POST['password']))
            {
                die("Missing fields");
            }

            $username = $_POST['username'];
            $password = $_POST['password'];
    
            $result = $this->model->login([$username, $password]);
    
            if($result){
                Session::set("userId", $result["uuid"]);
                $this->profile();
            }else{
                die("Wrong credentials");
            }
        } else {
            $this->view("login");
        }
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

        if($this->model->checkIfExists("email", $email)) {
            die("A user with this e-mail address is already registered!"); // ERRMSG")
        }
        
        if($this->model->checkIfExists("username", $username)) {
            die("Username already exists");
        }

        $userId = uniqid("u.", true); 
        
        while($this->model->checkIfExists("user_id", $userId)){
            $userId = uniqid("u.", true);
        }

        $password = password_hash($password, PASSWORD_DEFAULT);

        $this->model->create([$userId, $username, $password, $email]) ? print("User created") : die("Error"); // ERRMSG
    }

    public function profile() { // TEST
        $this->view("profile", ["userId" => Session::checkUserSession()]);
    }
}
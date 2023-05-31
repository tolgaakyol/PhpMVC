<?php

namespace Controllers;

use System\Controller;
use System\Session;
use System\Error;

class User extends Controller {    
    private \Models\User $model;
    
    public function __construct()
    {
        $this->model = $this->model('User');
    }

    public function home(): void // TEST
    {
        if(Session::checkIfAuthorized(2)){
          echo "<pre>";
          print_r($this->model->list());
          echo "</pre>";
        } else if (Session::checkIfAuthorized(2, true) === Error::session_Unauthorized){
          die("You are not allowed to view this page!");
        } else {
          header("Location: ../user/login");
        }
    }

    public function login(): void //
    {
        if (Session::checkIfAuthorized()) {
            header("Location: ../user/profile");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if(!isset($_POST['username']) || !isset($_POST['password']))
            {
                die("Missing fields");
            }

            $username = htmlspecialchars($_POST['username']);
            $password = htmlspecialchars($_POST['password']);
    
            $result = $this->model->login([$username, $password]);

            if($result){
                Session::createUserSession($result['user_id'], $result['username'], $result['level']);

                # Redirect to profile page
                header("Location: ../user/profile");
            }else{
                die("Wrong credentials");
            }
        } else {
            $this->view("login");
        }
    }

    public function create(): void
    {
        if (Session::checkIfAuthorized()) {
            header("Location: ../user/profile");
        }

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
            die("A user with this e-mail address is already registered!"); // ERRMSG
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

    public function profile(): void { // TEST
        if (!Session::checkIfAuthorized()) {
            $this->logout();
        }

        $this->view("profile", ["username" => Session::get('username')]);
    }

    public function logout(): void {
        if (!Session::checkIfAuthorized()) {
            header("Location: ../user/login");
        }

        Session::logout();
        header("Location: ../user/login");
    }
}
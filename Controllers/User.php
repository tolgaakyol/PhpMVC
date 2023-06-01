<?php

namespace Controllers;

use System\Controller;
use System\Session;
use System\Error;
use Helpers\Generator;

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
          echo '<br/>';
          print_r(Session::createUserAuthCookie('a12'));
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

            if(!isset($_POST[LOGIN_WITH]) || !isset($_POST['password']))
            {
                die("Missing fields");
            }

            $login = htmlspecialchars($_POST[LOGIN_WITH]);
            $password = htmlspecialchars($_POST['password']);
    
            $result = $this->model->login([$login, $password]);

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

        // TODO: Build a proper input control method
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
        $password_confirm = filter_input(INPUT_POST, 'password_confirm', FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

        if(!$username || !$password_confirm || !$password || !$email) {
            die("Please fill in all required fields with valid information!"); // ERRMSG
        }

        if($password != $password_confirm){
            die("Passwords do not match"); // ERRMSG
        }

        if($this->model->checkIfExists("email", $email)) {
            if(empty($email)) {
                die('Please fill in all required fields with valid information!');
            }
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
<?php

namespace Controllers;

use Helpers\Generator;
use NonceUseCase;
use System\Controller;
use System\Session;
use System\Error;

class User extends Controller
{
  private \Models\User $model;

  public function __construct()
  {
    $this->model = $this->model('User');
    Session::validateCookie();
  }

  public function home(): void // TEST
  {
    if (Session::checkIfAuthorized(2)) {
      echo "<pre>";
      print_r($this->model->list());
      echo '<br/>';
      Session::validateCookie();
      echo "</pre>";
    } else if (Session::checkIfAuthorized(2, true) === Error::session_Unauthorized) {
      die("You are not allowed to view this page!"); // ERRMSG
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

      if (!isset($_POST[LOGIN_WITH]) || !isset($_POST['password'])) {
        die("Missing fields"); // ERRMSG
      }

      $login = htmlspecialchars($_POST[LOGIN_WITH]);
      $password = htmlspecialchars($_POST['password']);

      $result = $this->model->login([$login, $password]);

      if (!$result) {
        die("Wrong credentials"); // ERRMSG
      }

      Session::createUserSession($result['user_id']);

      if (htmlspecialchars($_POST['remember']) == 1) {
        Session::createUserAuthCookie($result['user_id']);
      }

      # Redirect to profile page
      header("Location: ../user/profile");

    } else {
      $this->view("login");
    }
  }

  public function create(): void
  {
    if (Session::checkIfAuthorized()) {
      header("Location: ../user/profile");
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      // TODO: Build a proper input control method
      $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
      $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
      $password_confirm = filter_input(INPUT_POST, 'password_confirm', FILTER_SANITIZE_SPECIAL_CHARS);
      $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

      if (strlen($username) > 30 || strlen($password) > 30) {
        die("Max length for username and password is 30!"); // ERRMSG
      }

      if (!$username || !$password_confirm || !$password || !$email || strlen($email) > 255) {
        die("Please fill in all required fields with valid information!"); // ERRMSG
      }

      if ($password != $password_confirm) {
        die("Passwords do not match"); // ERRMSG
      }

      if ($this->model->checkIfExists("email", $email)) {
        if (empty($email)) {
          die('Please fill in all required fields with valid information!'); // ERRMSG
        }
        die("A user with this e-mail address is already registered!"); // ERRMSG
      }

      if ($this->model->checkIfExists("username", $username)) {
        die("Username already exists"); // ERRMSG
      }

      $userId = uniqid("u.", true);

      while ($this->model->checkIfExists("user_id", $userId)) {
        $userId = uniqid("u.", true);
      }

      $password = password_hash($password, PASSWORD_DEFAULT);

      $result = $this->model->create([$userId, $username, $password, $email, DEFAULT_USER_LEVEL]);

      if(!$result) {
        die("Error while creating the user!"); // ERRMSG
      }

      if(REQUIRE_EMAIL_ACTIVATION) {
        $result = $this->generateNonce($userId, 1, NonceUseCase::Activation->value);
      }

      if(REQUIRE_EMAIL_ACTIVATION && !$result) {
        die('Unable to create user activation token.'); // ERRMSG
      }

      print('User created with no errors'); // TODO: Complete user creation

    } else {
      $this->view("create");
    }
  }

  public function profile(): void
  { // TEST
    if (!Session::checkIfAuthorized()) {
      $this->logout();
    }

    $this->view("profile", ["username" => Session::get('username')]);
  }

  public function logout(): void
  {
    if (!Session::checkIfAuthorized()) {
      header("Location: ../user/login");
    }

    Session::logout();
    header("Location: ../user/login");
  }

  /**
   *
   * @param string $userId Pass in the $userId for which the token is generated.
   * @param int $lifespan Pass in the desired lifespan of the token in days.
   * @param int|null $useCase (optional) Pass in a NonceUseCase->value (activation & reset password etc.), only for debug purposes.
   * @return bool|Error
   *
   */
  public function generateNonce(string $userId, int $lifespan, int|null $useCase = null): bool|Error
  {
    $token = Generator::randomToken(50);
    while ($this->model->checkIfExists('token', $token, false, 'nonces')) {
      $token = Generator::randomToken(50);
    }

    try {
      $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Istanbul'));
      $expiresAt = $now->add(new \DateInterval('P' . $lifespan . 'D'))->format('YmdHis');
    } catch (\Exception $e) {
      return false;
    }

    $content = array(
      'user_id' => $userId,
      'token' => $token,
      'expires_at' => $expiresAt
    );

    if(!is_null($useCase)) { $content['use_case'] = $useCase; }

    return $this->model->storeNonce($content);
  }
}
<?php

namespace TolgaAkyol\PhpMVC\Controllers;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use TolgaAkyol\PhpMVC\{Helpers\Generator,
    Helpers\InputFilter
};
use TolgaAkyol\PhpMVC\System\{Controller, Session, Error, Log, LogType};
use TolgaAkyol\PhpMVC\Config\TokenUseCase;
use TolgaAkyol\PhpMVC\Models\User as Model;

/** @noinspection PhpUnused */
class User extends Controller
{
  private Model $model;

  public function __construct()
  {
    $this->model = $this->model('User', true);
    if(!Session::checkIfAuthorized()) {
      Session::validateCookie();
    }
  }

  public function home(): void // TEST
  {
    if (Session::checkIfAuthorized(1)) {
      echo "<pre>";
      print_r($this->model->list());
      echo "</pre>";
    } else {
      die("You are not allowed to view this page!"); // ERRMSG
    }
  }

  public function login(): void //
  {
    if (Session::checkIfAuthorized()) {
      header("Location: /user/profile");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $filter = new InputFilter();

      $filter   ->post(LOGIN_WITH)
                ->required()

                ->post('password')
                ->required()

                ->post('remember')
                ->length(0,1);

      if($filter->getErrors()) {
        $this->view('User/Login', ['errors' => $filter->getErrors()], true);
        return;
      }

      $login = $filter->getValues()[LOGIN_WITH];
      $password = $filter->getValues()['password'];
      $remember = $filter->getValues()['remember'];

      $result = $this->model->login([$login, $password]);

      if (!$result) {
        die("Wrong credentials"); // ERRMSG
      }

      Session::createUserSession($result['user_id']);

      if ($remember == 1) {
        Session::createUserAuthCookie($result['user_id']);
      }

      # Redirect to profile page
      header("Location: /user/profile");

    } else {
      $this->view("User/Login", null, true);
    }
  }

  /** @noinspection PhpUnused */
  public function create(): void
  {
    if (Session::checkIfAuthorized()) {
      header("Location: ../user/profile");
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $filter = new InputFilter();
      $filter ->post('username')
              ->required()
              ->lettersOnly()
              ->length(USERNAME_LENGTH_MIN, USERNAME_LENGTH_MAX)

              ->post('email')
              ->required()
              ->email()

              ->post('password')
              ->required()
              ->alphanumeric()
              ->length(PASSWORD_LENGTH_MIN, PASSWORD_LENGTH_MAX)

              ->post('password_confirm')
              ->required()
              ->equalTo('password');

      if($filter->getErrors()) {
        $this->view('User/Create', ['errors' => $filter->getErrors()], true);
        return;
      }

      $username = $filter->getValues()['username'];
      $email = $filter->getValues()['email'];
      $password = $filter->getValues()['password'];

      if ($this->model->checkIfExists("email", $email)) {
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
        $result = $this->generateToken($userId, TokenUseCase::Activation->value, '7D');
      }

      if(REQUIRE_EMAIL_ACTIVATION && !$result) {
        die('Unable to create user activation token!'); // ERRMSG
      }

      print('User was created successfully!'); // TODO: Complete user creation

    } else {
      $this->view("User/Create", null, true);
    }
  }

  public function profile(): void
  { // TEST
    if (!Session::checkIfAuthorized()) {
      $this->logout();
    }

    $this->view("User/Profile", ["username" => Session::get('username')], true);
  }

  public function logout(): void
  {
    if (!Session::checkIfAuthorized()) {
      header("Location: ../user/login");
    }

    Session::logout();
    header("Location: ../user/login");
  }

  /** @noinspection PhpUnused */
  public function activate($token = ''): void {
    $validToken = $this->validateToken($token, TokenUseCase::Activation->value, 50);

    $result = $this->model->activateUser($validToken['user_id']);

    if(!$result) { die('Unable to activate user!'); }

    print('User has been activated successfully!');
  }

  /** @noinspection PhpUnused */
  public function recover($token = ''): void {
    if($_SERVER['REQUEST_METHOD'] == 'POST' && empty($token)) {
      $filter = new InputFilter();
      $filter ->post('email')
              ->required()
              ->email();

      if($filter->getErrors()) {
        $this->view('User/RequestRecovery', ['errors' => $filter->getErrors()], true);
        return;
      }

      $email = $filter->getValues()['email'];

      if(!$this->model->checkIfExists('email', $email)) { die('E-mail address not found!'); } // ERRMSG

      $userId = $this->model->getUserIdByKey('email', $email);

      if($userId) {
        $token = $this->generateToken($userId, TokenUseCase::ResetPassword->value, 'T6H', true);
        // TODO: Send an email that includes the link: "domain.com/user/recover/update/$token"
        print('Password recovery link has been sent to your e-mail address: ' . $token);
      } else {
        die('Unable to process the request.'); // ERRMSG
      }
    } else if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($token)) {
      $validToken = $this->validateToken($token, TokenUseCase::ResetPassword->value, 50);
      $user = $this->model->getUserByKey('user_id', $validToken['user_id']);

      if(!$user) { die('Unable to retrieve user information from the server!'); } // ERRMSG

      $filter = new InputFilter();
      $filter ->post('password')
              ->required()
              ->alphanumeric()
              ->length(PASSWORD_LENGTH_MIN, PASSWORD_LENGTH_MAX)

              ->post('password_confirm')
              ->required()
              ->equalTo('password');

      if($filter->getErrors()) {
        $this->view('User/NewPassword', ['email' => $user['email'], 'errors' => $filter->getErrors()], true);
        return;
      }

      $password = $filter->getValues()['password'];

      $result = $this->model->updatePassword($validToken['user_id'], password_hash($password, PASSWORD_DEFAULT));

      if($result) {
        $this->model->destroyToken($token);
        print('Password was changed successfully!'); // TODO: Proper action after user password change
      } else {
        Log::toFile(LogType::Error, __METHOD__, 'Unable to update the password of user: ' . $validToken['user_id']);
        die ('Unable to update password!'); // ERRMSG
      }

    } else if (!empty($token)) {
      $validToken = $this->validateToken($token, TokenUseCase::ResetPassword->value, 50);
      $user = $this->model->getUserByKey('user_id', $validToken['user_id']);

      if(!$user) { die('Unable to retrieve user information from the server!'); } // ERRMSG

      $this->view('User/NewPassword', ['email' => $user['email']], true);
    } else {
      $this->view('User/RequestRecovery', null, true);
    }
  }

  private function validateToken(string $token, int $useCase, int $length = 0) {
    if(empty($token)) { die('You must provide a token'); } // ERRMSG
    if($length > 0 && strlen($token) != $length) { die('Invalid token!'); } // ERRMSG

    $token = $this->model->getToken($token, $useCase);

    if(!$token) { die('Invalid token!'); } // ERRMSG

    try {
      $now = new DateTime('now', new DateTimeZone('Europe/Istanbul'));
      $now = $now->format('YmdHis');
    } catch (Exception $e) {
      Log::toFile(LogType::Error, __METHOD__, 'Unable to capture current timestamp: ' . $e->getMessage());
      die('Unable to validate token!'); // ERRMSG
    }

    if((int) $now > (int) $token['expires_at']) {
      $this->model->destroyToken($token['token']);
      die('Token expired!'); // ERRMSG
    }
    return $token;
  }

  /**
   *
   * @param string $userId Pass in the $userId for whom the token will be generated.
   * @param int $useCase Pass in a TokenUseCase->value (activation & reset password etc.).
   * @param string $lifespan (optional) Pass in the desired lifespan of the token as 1D (for 1 day), 2M (for 2 months) etc. Default lifespan is 1 day.
   * @param bool $returnToken (optional) While true, the function will return the generated token as a string if the operation is successful.
   * @return bool|string|Error
   *
   */
  private function generateToken(string $userId, int $useCase, string $lifespan = '1D', bool $returnToken = false): bool|string|Error
  {
    $this->model->avoidDuplicateToken($userId, $useCase);

    $token = Generator::randomToken(50);
    while ($this->model->checkIfExists('token', $token, false, 'tokens')) {
      $token = Generator::randomToken(50);
    }

    try {
      $now = new DateTimeImmutable('now', new DateTimeZone('Europe/Istanbul'));
      $expiresAt = $now->add(new DateInterval('P' . $lifespan))->format('YmdHis');
    } catch (Exception $e) {
      Log::toFile(LogType::Error, __METHOD__, 'Unable to capture current timestamp: ' . $e->getMessage());
      return false;
    }

    $content = array(
      'user_id' => $userId,
      'token' => $token,
      'expires_at' => $expiresAt,
      'use_case' => $useCase
    );

    $result = $this->model->storeToken($content);

    return ($returnToken && $result) ? $token : $result;
  }
}
<?php

namespace System;

use Helpers\Reformatter;

class Session
{
  private static \Models\Session $model;

  public function __construct()
  {
    session_start();
  }

  public static function initializeModel(): void {
    if(empty(self::$model)){
      self::$model = new \Models\Session();
    }
  }

  public static function set(array $sessionData): void {
    foreach($sessionData as $key => $value) {
      $_SESSION[$key] = $value;
    }
  }

  public static function get($key) {
    if(isset($_SESSION[$key])) {
      return $_SESSION[$key];
    }

    return false;
  }

  public static function createUserSession(string $userId, string $username, int $level): void {
      self::initializeModel();

      $sessionData = array(
        'token' => password_hash($userId, PASSWORD_DEFAULT),
        'ipv4' => Reformatter::ipv4($_SERVER['REMOTE_ADDR']),
        'level' => $level
      );

      self::$model->storeSessionToken($sessionData);

      $sessionData['username'] = $username;
      self::set($sessionData);
  }

  public static function checkIfUserSessionExists(): bool {
    if(empty($_SESSION['token'])) {
      return false;
    }

    if(empty($_SESSION['username'])) {
      return false;
    }

    if(empty($_SESSION['ipv4'])) {
      return false;
    }    

    return true;
  }

  public static function checkIfAuthorized(): bool {
    self::initializeModel();

    $session = self::checkIfUserSessionExists() ? self::$model->getStoredSession(self::get('token')) : false;
    $user = $session ? self::$model->getUser(self::get('username')) : false;

    if(!$user || !$session){ return false; }

    if(!password_verify($user['user_id'], $session['token'])) { return false; }

    if(ip2long($_SERVER['REMOTE_ADDR']) != $session['ipv4']) { return false; }

    if($user['level'] != $session['level']) { return false; }

    return true;
  }

  public static function logout(): void {
    self::initializeModel();

    self::$model->logout(self::get('token'));
    self::destroy();
  }

  public static function destroy(): void
  {
    session_destroy();
  }
}
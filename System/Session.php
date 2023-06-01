<?php

namespace System;

use Cassandra\Date;
use Helpers\Reformatter, Helpers\Generator;
use System\Error;

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

  public static function createUserAuthCookie(string $userId): array {
    $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Istanbul'));
    $lifespan = \DateInterval::createFromDateString('15 days');
    $expire = $now->add($lifespan);

    $cookieData = array(
      'user_id' => $userId,
      'token' => Generator::randomToken(50),
      'expires_at' => $expire->format('YmdHis'),
      'ipv4' => Reformatter::ipv4($_SERVER['REMOTE_ADDR']),
      'device' => $_SERVER['HTTP_USER_AGENT'],
      'os' => '',
      'browser' => ''
    );

    return $cookieData;
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

  /**
   *
   * @param int $level (optional) Pass in the permitted user level to restrict access to those who are below the limit.
   * @param bool $returnError (optional) When passed in 'true', function will return the specific error as in the enumeration '\System\Error', instead of returning 'false'.
   * @return bool|Error
   *
   */
  public static function checkIfAuthorized(int $level = 0, bool $returnError= false): Error|bool {
    self::initializeModel();

    $session = self::checkIfUserSessionExists() ? self::$model->getStoredSession(self::get('token')) : false;
    $user = $session ? self::$model->getUser(self::get('username')) : false;

    if(!$user || !$session){
      return $returnError ? Error::session_Missing : false;
    }

    if(!password_verify($user['user_id'], $session['token'])) {
      self::logout();
      return $returnError ? Error::session_Corrupt : false;
    }

    if(ip2long($_SERVER['REMOTE_ADDR']) != $session['ipv4']) {
      // FIXME: IP address check is error-prone
      self::logout();
      return $returnError ? Error::session_NetworkChanged : false;
    }

    if($user['level'] != $session['level']) {
      self::logout();
      return $returnError ? Error::session_LevelMismatch : false;
    }

    if($level > 0 && $user['level'] < $level){
      return $returnError ? Error::session_Unauthorized : false;
    }

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
<?php

namespace System;

use Helpers\Reformatter, Helpers\Generator;

class Session
{
  private static \Models\Session $model;

  public function __construct()
  {
    session_start();
  }

  public static function initializeModel(): void
  {
    if (empty(self::$model)) {
      self::$model = new \Models\Session();
    }
  }

  public static function set(array $sessionData): void
  {
    foreach ($sessionData as $key => $value) {
      $_SESSION[$key] = $value;
    }
  }

  public static function get($key)
  {
    if (isset($_SESSION[$key])) {
      return $_SESSION[$key];
    }

    return false;
  }

  public static function createUserSession(string $userId): void
  {
    self::initializeModel();

    $user = self::$model->getUserByKey(self::$model::id, $userId);

    $sessionData = array(
        'username' => $user['username'],
        'token' => password_hash($userId, PASSWORD_DEFAULT),
        'ipv4' => Reformatter::ipv4($_SERVER['REMOTE_ADDR']),
        'level' => $user['level']
    );

    self::$model->storeSessionToken($sessionData);
    self::set($sessionData);
  }

  public static function createUserAuthCookie(string $userId): bool
  {
    // FIXME: Refactor expire date variables.

    $expiresAtDb = '';
    $expiresAtCookie = '';

    try {
      $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Istanbul'));
      $lifespan = \DateInterval::createFromDateString('15 days');
      $expiresAt = $now->add($lifespan);
      $expiresAtDb = $expiresAt->format('YmdHis');
      $expiresAtCookie = $expiresAt->format('U');
    } catch (\Exception $e) {
      Log::toFile(LogType::Error, __METHOD__, 'DateTimeImmutable class is unable to capture a timestamp.');
      return false;
    }

    $userAgent = new \WhichBrowser\Parser(getallheaders());

    $token = Generator::randomToken(50);

    $dbData = array(
        'user_id' => $userId,
        'token' => $token,
        'expires_at' => $expiresAtDb,
        'ipv4' => Reformatter::ipv4($_SERVER['REMOTE_ADDR']),
        'device' => $userAgent->device->type,
        'os' => $userAgent->os->toString(),
        'browser' => $userAgent->browser->toString()
    );

    if (self::$model->storeAuthCookie($dbData)) {
      setcookie('auth', $token, $expiresAtCookie, '/', URL_ROOT, HTTPS_ENABLED, true);
      return true;
    } else {
      return false;
    }
  }

  public static function checkIfUserSessionExists(): bool
  {
    if (empty($_SESSION['token'])) {
      return false;
    }

    if (empty($_SESSION['username'])) {
      return false;
    }

    if (empty($_SESSION['ipv4'])) {
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
  public static function checkIfAuthorized(int $level = 0, bool $returnError = false): Error|bool
  {
    self::initializeModel();

    $session = self::checkIfUserSessionExists() ? self::$model->getStoredEntryByToken(self::$model::session, self::get('token')) : false;
    $user = $session ? self::$model->getUserByKey(self::$model::name, self::get('username')) : false;

    if (!$user || !$session) {
      return $returnError ? Error::session_Missing : false;
    }

    if (!password_verify($user['user_id'], $session['token'])) {
      self::logout();
      return $returnError ? Error::session_Corrupt : false;
    }

    if (ip2long($_SERVER['REMOTE_ADDR']) != $session['ipv4']) {
      // FIXME: IP address check is error-prone
      self::logout();
      return $returnError ? Error::session_NetworkChanged : false;
    }

    if (ROLE_CHANGE_REQ_LOGIN && $user['level'] != $session['level']) {
      self::logout();
      return $returnError ? Error::session_LevelMismatch : false;
    }

    if ($level > 0 && $user['level'] < $level) {
      return $returnError ? Error::session_Unauthorized : false;
    }

    return true;
  }

  public static function validateCookie(): bool {
    self::initializeModel();

    if(isset($_COOKIE['auth'])) {
      $dbEntry = self::$model->getStoredEntryByToken(self::$model::cookie, htmlspecialchars($_COOKIE['auth']));

      if(!$dbEntry) {
        return false;
      }

      try {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Istanbul'));
        $now = $now->format('YmdHis');
      } catch (\Exception $e) {
        return false;
      }

      if((int) $now > (int) $dbEntry['expires_at']) {
        self::unsetCookie('auth');
        return false;
      }

      $userAgent = new \WhichBrowser\Parser(getallheaders());

      $userAgent = array(
          'device' => $userAgent->device->type,
          'os' => $userAgent->os->toString(),
          'browser' => $userAgent->browser->toString());

      if($dbEntry['device'] != $userAgent['device'] || $dbEntry['os'] != $userAgent['os'] || $dbEntry['browser'] != $userAgent['browser']){
        self::unsetCookie('auth');
        return false;
      }

      self::createUserSession($dbEntry['user_id']);
      return true;
    } else {
      return false;
    }
  }

  public static function unsetCookie(string $name): void {
    setcookie($name, '', time()-86401, "/", URL_ROOT, HTTPS_ENABLED, false);
  }

  public static function logout(): void
  {
    self::initializeModel();
    self::unsetCookie('auth');
    self::$model->logout(self::get('token'), self::get('username'));
    self::destroy();
  }

  public static function destroy(): void
  {
    session_destroy();
  }
}
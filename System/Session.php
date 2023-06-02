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

  public static function createUserSession(string $userId, bool $regenSessionId = true, string|null $forcedSessionId = null): void
  {
    if(MULTI_SESSION_LIMIT > 0) {
      $count = self::$model->countUserSessions($userId);

      if($count >= MULTI_SESSION_LIMIT) {
        self::$model->destroyUserSession($userId);
      }
    }

    if($regenSessionId) {
      if(!is_null($forcedSessionId)) {
        die('Invalid operation!'); // ERRMSG
      }

      self::destroy();
      session_regenerate_id();
      session_start();
    }

    if(!$regenSessionId && !is_null($forcedSessionId)) {
      self::destroy();
      session_id($forcedSessionId);
      session_start();
    }

    self::initializeModel();

    $user = self::$model->getUserByKey(self::$model::id, $userId);

    $sessionData = array(
        'user_id' => $userId,
        'username' => $user['username'],
        'ipv4' => Reformatter::ipv4($_SERVER['REMOTE_ADDR']),
        'level' => $user['level']
    );

    self::set($sessionData);
    unset($sessionData['username']);
    $sessionData['session_id'] = session_id();
    self::$model->storeSessionToken($sessionData);
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
        'session_id' => session_id(),
        'secret' => $token,
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
    if (empty($_SESSION['user_id']) || empty($_SESSION['username']) || empty($_SESSION['ipv4']) || empty($_SESSION['level'])) {
      return false;
    } else {
      return true;
    }
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

    $session = self::checkIfUserSessionExists() ? self::$model->getStoredEntry(self::$model::session, session_id()) : false;
    $user = $session ? self::$model->getUserByKey(self::$model::id, $session['user_id']) : false;

    if (!$user || !$session) {
      return $returnError ? Error::session_Missing : false;
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
      $dbEntry = self::$model->getStoredEntry(self::$model::cookie, htmlspecialchars($_COOKIE['auth']));

      if(!$dbEntry) {
        return false;
      }

      try {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Istanbul'));
        $now = $now->format('YmdHis');
      } catch (\Exception $e) {
        Log::toFile(LogType::Error, __METHOD__, 'Unable to capture current timestamp: ' . $e->getMessage());
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

      self::createUserSession($dbEntry['user_id'], false, $dbEntry['session_id']);
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
    self::$model->logout();
    self::destroy();
    $_SESSION = [];
  }

  public static function destroy(): void
  {
    session_destroy();
  }
}
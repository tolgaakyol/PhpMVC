<?php

namespace TolgaAkyol\PhpMVC\System;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use TolgaAkyol\PhpMVC\Helpers\{Geolocation, Reformatter, Generator};
use TolgaAkyol\PhpMVC\Models\Session as Model;
use WhichBrowser\Parser;

class Session
{
  private static Model $model;

  public function __construct() {
    session_start();
  }

  public static function initializeModel(): void {
    if (empty(self::$model)) {
      self::$model = new Model();
    }
  }

  public static function set(array $sessionData): void {
    foreach ($sessionData as $key => $value) {
      $_SESSION[$key] = $value;
    }
  }

  public static function get($key) {
    if (isset($_SESSION[$key])) {
      return $_SESSION[$key];
    }

    return false;
  }

  public static function createUserSession(string $userId, bool $regenSessionId = true, string|null $forcedSessionId = null): void {
    try {
      if(constant('MULTI_SESSION_LIMIT') > 0) {
        $count = self::$model->countUserSessions($userId);

        if($count >= constant('MULTI_SESSION_LIMIT')) {
          self::$model->destroyUserSession($userId);
        }
      }
    } catch (\Error $e) {
      Log::toFile(LogType::Critical, __METHOD__, $e->getMessage());
      Controller::systemError(__METHOD__, $e->getMessage());
    }

    if($regenSessionId) {
      if(!is_null($forcedSessionId)) {
        Controller::systemError(__METHOD__, 'Invalid operation!');
      }

      session_regenerate_id();
    }

    if(!$regenSessionId && !is_null($forcedSessionId)) {
      self::destroy();
      session_id($forcedSessionId);
      session_start();
    }

    self::initializeModel();

    $user = self::$model->getUserByKey(self::$model::id, $userId);

    $userAgent = new Parser(getallheaders());

    $geolocation = Geolocation::get($_SERVER['REMOTE_ADDR']);

    $sessionData = array(
      'user_id' => $userId,
      'username' => $user['username'],
      'ipv4' => Reformatter::ipv4($_SERVER['REMOTE_ADDR']),
      'level' => $user['level'],
      'device' => $userAgent->device->type,
      'os' => substr($userAgent->os->toString(), 0, 15),
      'browser' => $userAgent->browser->toString(),
      'country' => $geolocation['country'],
      'city' => $geolocation['city']
    );

    self::set($sessionData);
    unset($sessionData['username']);
    unset($sessionData['device']);
    unset($sessionData['os']);
    unset($sessionData['browser']);
    unset($sessionData['country']);
    unset($sessionData['city']);
    $sessionData['session_id'] = session_id();
    self::$model->storeSessionToken($sessionData);
  }

  public static function createUserAuthCookie(string $userId): bool {
    // FIXME: Refactor expire date variables.

    try {
      $now = new DateTimeImmutable('now', new DateTimeZone('Europe/Istanbul'));
      $lifespan = DateInterval::createFromDateString('15 days');
      $expiresAt = $now->add($lifespan);
      $expiresAtDb = $expiresAt->format('YmdHis');
      $expiresAtCookie = $expiresAt->format('U');
    } catch (Exception $e) {
      Log::toFile(LogType::Error, __METHOD__, 'DateTimeImmutable class is unable to capture a timestamp: ' . $e->getMessage());
      return false;
    }

    $userAgent = new Parser(getallheaders());

    $token = Generator::randomToken(50);

    $dbData = array(
        'user_id' => $userId,
        'session_id' => session_id(),
        'secret' => $token,
        'expires_at' => $expiresAtDb,
        'ipv4' => Reformatter::ipv4($_SERVER['REMOTE_ADDR']),
        'device' => $userAgent->device->type,
        'os' => substr($userAgent->os->toString(), 0, 15),
        'browser' => $userAgent->browser->toString()
    );

    if (self::$model->storeAuthCookie($dbData)) {
      try {
        setcookie('auth', $token, $expiresAtCookie, '/', constant('URL_ROOT'), constant('HTTPS_ENABLED'), true);
      } catch (\Error $e) {
        Log::toFile(LogType::Critical, __METHOD__, $e->getMessage());
        Controller::systemError(__METHOD__, $e->getMessage());
      }
      return true;
    } else {
      return false;
    }
  }

  public static function checkIfUserSessionExists(): bool {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['ipv4']) || !isset($_SESSION['level'])) {
      return false;
    } else {
      return true;
    }
  }

  public static function isLoggedIn(): bool {
    if(!self::checkIfUserSessionExists()) {
      return false;
    }

    return self::checkIfAuthorized();
  }

  /**
   *
   * @param int $level (optional) Pass in the permitted user level to restrict access to those who are below the limit.
   * @return bool
   *
   */
  public static function checkIfAuthorized(int $level = 0): bool {
    self::initializeModel();

    $session = self::checkIfUserSessionExists() ? self::$model->getStoredEntry(self::$model::session, session_id()) : false;
    $user = $session ? self::$model->getUserByKey(self::$model::id, $session['user_id']) : false;

    if (!$user || !$session) {
      return false;
    }

    if (ip2long($_SERVER['REMOTE_ADDR']) != $session['ipv4']) {
      // FIXME: IP address check is error-prone
      self::logout();
      return false;
    }

    try {
      if (constant('ROLE_CHANGE_REQ_LOGIN') && $user['level'] != $session['level']) {
        self::logout();
        return false;
      }
    } catch (\Error $e) {
      Log::toFile(LogType::Critical, __METHOD__, $e->getMessage());
      return false;
    }

    if ($level > 0 && $user['level'] < $level) {
      return false;
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
        $now = new DateTimeImmutable('now', new DateTimeZone('Europe/Istanbul'));
        $now = $now->format('YmdHis');
      } catch (Exception $e) {
        Log::toFile(LogType::Error, __METHOD__, 'Unable to capture current timestamp: ' . $e->getMessage());
        return false;
      }

      if((int) $now > (int) $dbEntry['expires_at']) {
        self::unsetCookie('auth');
        return false;
      }

      $userAgent = new Parser(getallheaders());

      $userAgent = array(
          'device' => $userAgent->device->type,
          'os' => substr($userAgent->os->toString(), 0, 15),
          'browser' => $userAgent->browser->toString());

      if($dbEntry['device'] != $userAgent['device'] || substr($dbEntry['os'], 0 ,15) != $userAgent['os'] || $dbEntry['browser'] != $userAgent['browser']){
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
    try {
      setcookie($name, '', time()-86401, "/", constant('URL_ROOT'), constant('HTTPS_ENABLED'), false);
    } catch (\Error $e) {
      Log::toFile(LogType::Critical, __METHOD__, $e->getMessage());
      Controller::systemError(__METHOD__, $e->getMessage());
    }
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
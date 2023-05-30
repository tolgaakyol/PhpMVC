<?php

namespace System;

use Helpers\Reformatter;

class Session
{
  public function __construct()
  {
    session_start();  
  }

  public static function set($sessionData) {
    foreach($sessionData as $key => $value) {
      $_SESSION[$key] = $value;
    }
  }

  public static function get($key) {
    if(isset($_SESSION[$key])) {
      return $_SESSION[$key];
    }
  }

  public static function createSessionToken($userId) {
    $token = array(
      'token' => password_hash($userId, PASSWORD_DEFAULT),
      'ipv4' => Reformatter::ipv4($_SERVER['REMOTE_ADDR'])
    );

    return $token;
  }

  // TODO: Validate token?
  public static function checkUserSession() {
    if(!isset($_SESSION['token']) || empty($_SESSION['token'])) {  
      return false;
    }

    return $_SESSION['token'];
  }

  public static function destroy()
  {
    session_destroy();
  }
}
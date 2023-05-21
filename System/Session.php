<?php

namespace System;

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

  // TODO: Validate token?
  public static function checkUserSession() {
    if(!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {  
      return false;
    }

    return $_SESSION['user_id'];
  }

  public static function destroy()
  {
    session_destroy();
  }
}
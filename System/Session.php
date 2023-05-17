<?php

namespace System;

class Session
{
  public function __construct()
  {
    session_start();  
  }

  public static function set(string $tag, string $userId) {
    $_SESSION[$tag] = $userId;
  }

  // TODO: Validate token?
  public static function checkUserSession() {
    if(!isset($_SESSION['userId']) || empty($_SESSION['userId'])) {  
      return false;
    }

    return $_SESSION['userId'];
  }

  public function destroy()
  {
    session_destroy();
  }
}
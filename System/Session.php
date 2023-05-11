<?php

namespace System;

class Session
{
  public function __construct()
  {
    session_start();  
  }

  public function createUserSession(string $userId) {
    // $_SESSION['username'] = $username;
    // $_SESSION['password'] = $password;
    $_SESSION['token'] = md5(uniqid()); // TODO: validate token
  }

  public function checkUserSession() {
    if(!isset($_SESSION['username']) || !isset($_SESSION['password']) || !isset($_SESSION['token']))  {
      return false;
    }

    return true;
    // TODO: validate token
  }

  public function destroy()
  {
    session_destroy();
  }
}
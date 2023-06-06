<?php

// TODO: definition & info

namespace Tolgaakyol\PhpMVC\Helpers;

class Generator {
  public static function randomToken(int $length = 20): string {
    $token = '';

    if($length % 2 > 0) {
      $length += 1;
    }

    try {
      $token = random_bytes($length / 2);
    } catch (\Exception $e) {
      return false;
    }

    return bin2hex($token);
  }
}
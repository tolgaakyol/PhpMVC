<?php

// TODO: definition & info

namespace Helpers;

class Reformatter {
  public static function ipv4($ipv4):int {
    $ipv4 = ip2long($ipv4); // FIXME: Requires modification for 32-bit systems

    if($ipv4 <= 0) { return 0; }
    return $ipv4;
  }
}
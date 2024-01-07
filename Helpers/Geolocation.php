<?php

// TODO: definition & info

// Uses ip-api.com

namespace TolgaAkyol\PhpMVC\Helpers;

class Geolocation {
  public static function get($ip): array|false {
    $url = 'http://ip-api.com/json/' . $ip . '?fields=16402';

    $json = file_get_contents($url);

    $geolocation = json_decode($json);

    if($geolocation->status === 'success') {
      return ['country' => $geolocation->countryCode, 'city' => $geolocation->city];
    } else {
      return ['country' => '', 'city' => ''];
    }
  }
}
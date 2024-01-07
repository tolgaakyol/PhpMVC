<?php

// TODO: definition & info

// Uses ip-api.com

namespace TolgaAkyol\PhpMVC\Helpers;

class Geolocation {
  public static function get($ipv4): array|false {
    $api_url = 'http://ip-api.com/json/' . $ipv4 . '?fields=16402';

    $json_data = file_get_contents($api_url);

    $response_data = json_decode($json_data);

    if($response_data['status'] === 'success') {
      return ['country' => $response_data['countryCode'], 'city' => $response_data['city']];
    } else {
      return ['country' => '', 'city' => ''];
    }
  }
}
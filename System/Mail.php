<?php

namespace TolgaAkyol\PhpMVC\System;

use Mailjet\Client;
use \Mailjet\Resources;

class Mail {

  private Client $mj;
  private array $body;
  public function __construct($from, $to, $subject, $textContent, $htmlContent) {
    $apikey = constant('MJ_APIKEY_PUBLIC');
    $apisecret = constant('MJ_APIKEY_PRIVATE');

    $this->mj = new Client($apikey, $apisecret);

    $this->body = [
        'Messages' => [
            [
                'From' => [
                    'Email' => $from[0],
                    'Name' => $from[1]
                ],
                'To' => [
                    [
                        'Email' => $to[0],
                        'Name' => $to[1]
                    ]
                ],
                'Subject' => $subject,
                'TextPart' => $textContent,
                'HTMLPart' => $htmlContent
            ]
        ]
    ];
  }

  public function send(): array|false {
    $response = $this->mj->post(Resources::$Email, ['body' => $this->body]);

    if($response->success()) {
      return $response->getData();
    } else {
      return false;
    }
  }
}
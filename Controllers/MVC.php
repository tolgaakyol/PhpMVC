<?php

namespace TolgaAkyol\PhpMVC\Controllers;

use TolgaAkyol\PhpMVC\System\Controller;

if(!constant('USE_CORE_VIEWS')) {
  header('Location: ' . constant('URL_FULL'));
}

class MVC extends Controller {
    public function index(): void {
        $this->view("home", null, true);
    }
}
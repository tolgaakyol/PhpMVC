<?php

namespace TolgaAkyol\PhpMVC\Controllers;

use TolgaAkyol\PhpMVC\System\Controller;

class Home extends Controller {
    public function home(): void {
        $this->view("home", null, true);
    }
}
<?php

namespace Tolgaakyol\PhpMVC\Controllers;

use Tolgaakyol\PhpMVC\System\Controller;

class Page extends Controller {
    public function home(): void {
        // $this->view("home");
      phpinfo();
    }
}
<?php

namespace Controllers;
use System\Controller;

class Page extends Controller {
    public function home(): void
    {
        $this->view("home");
    }

    public function php(): void
    {
        phpinfo();
    }
}
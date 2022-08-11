<?php

namespace Controllers;
use System\Controller;

class Page extends Controller {
    public function __construct()
    {
        
    }

    public function home(){
        echo 'home';
    }

    public function about(){
        echo 'about';
    }

    public function php()
    {
        phpinfo();
    }
}
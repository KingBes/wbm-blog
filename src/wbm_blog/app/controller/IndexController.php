<?php

namespace plugin\wbm_blog\app\controller;

use support\Request;

class IndexController
{

    public function index()
    {
        return view('index/index', ['name' => 'wbm_blog']);
    }

}

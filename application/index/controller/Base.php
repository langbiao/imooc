<?php
namespace app\index\controller;

use think\Controller;

class Base extends Controller
{
    public function _initialize()
    {
        $this->assign('title', '慕课网');
        $this->assign('keywords', '慕课网');
        $this->assign('description', '慕课网');
    }
}

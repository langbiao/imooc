<?php
namespace app\index\controller;

use think\Controller;

class Base extends Controller
{
    public function _initialize()
    {
        if ($_SESSION['user']) {
            $is_login = 1;
            $this->assign('is_login', $is_login);
            $this->assign('user', $_SESSION['user']);
        }
//        dump($_SESSION['user']);die;
        $this->assign('title', '慕课网');
        $this->assign('keywords', '慕课网');
        $this->assign('description', '慕课网');
    }
}

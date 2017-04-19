<?php
namespace app\index\controller;

use think\Db;
use think\Controller;

class Login extends Controller
{

    // 登录操作
    public function index()
    {
        $username = $this->request->post('username', '');
        $password = $this->request->post('password', '');

        if (empty($username)) {
            $username_tips = '请输入正确的邮箱或手机号';
        }
        if (strpos($username, '@') !== false) {
            if (preg_match('/^[a-z\d]+(\.[a-z\d]+)*\@([\da-z](-[\da-z])?)+(\.{1,2}[a-z]+)+$/', $username) == 0) {
                $username_tips = '请输入正确的邮箱或手机号';
            } else {
                $is_email = 1;
            }
        } else {
            if (preg_match('/^1[3578]\d{9}$/', $username) == 0) {
                $username_tips = '请输入正确的邮箱或手机号';
            } else {
                $is_mobile = 1;
            }
        }

        if (strlen($password) < 6) {
            $password_tips = '请输入6-16位密码，区分大小写，不能使用空格！';
        }

        if (!empty($username_tips) || !empty($password_tips)) {
            return ['code'=>1001, 'msg1'=>$username_tips, 'msg2'=>$password_tips];
        }

        $userModel = Db::name('study_user');
        if ($is_email) {
            $userModel->where('mail', '=', $username);
        }
        if ($is_mobile) {
            $userModel->where('phone', '=', $username);
        }
        $res = $userModel->where('password', '=', md5($password))->find();

        if (!$res['id']) {
            return ['code'=>1002, 'msg'=>'账号或密码错误'];
        }
        if ($res['status'] != 1) {
            return ['code'=>1003, 'msg'=>'账号被禁用'];
        }
        $_SESSION['user'] = $res;

        return ['code'=>1000, 'msg'=>'ok'];
    }

    public function logout()
    {
        $_SESSION['user'] = null;
        unset($_SESSION['user']);

        $this->redirect('/');
    }
   
}

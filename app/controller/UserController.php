<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use app\model\User;
use think\facade\Session;
use think\Request;

class UserController extends BaseController
{
    public function index()
    {
        $user = new User();
        $user = $user->fetch(Session::get('user_id'));
        if (!$user) {
            return alert("error", "用户不存在", "2000", "/index");
        }
        return view('/user/index', ['user' => $user]);

    }

    public function login(): string
    {
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        if ($this->app->authService->userLogin($username, $password)) {
            return alert("success", "登录成功", "2000", "/user/index");
        } else {
            return alert("error", "用户名或密码错误", "2000", "/index");
        }
    }

    public function register(): string
    {
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        if ($this->app->authService->userRegister($username, $password)) {
            return alert("success", "注册成功", "2000", "/index");
        } else {
            return alert("error", "用户已存在", "2000", "/index");
        }
    }

    public function logout(): string
    {
        Session::delete('user_id');
        return alert("success", "登出成功", "2000", "/index");
    }
}

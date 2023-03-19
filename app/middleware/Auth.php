<?php
declare (strict_types = 1);

namespace app\middleware;

use Closure;
use think\facade\Session;

class Auth
{

    public function handle($request, Closure $next)
    {
        // 是否已经登陆
        if (Session::get('user_id')) {
            return response(alert("error", "您已登录", "2000", "/user/index"));
        }
        // 检查是否存在用户名密码
        if (!$request->post('username') || !$request->post('password')) {
            return response(alert("error", "用户名或密码不能为空", "2000", "/index"));
        }
        // 检查是否有登录或注册操作
        if ($request->post('login') xor $request->post('register')) {
            return $next($request);
        } else {
            return response(alert("error", "未知操作", "2000", "/index"));
        }
    }
}

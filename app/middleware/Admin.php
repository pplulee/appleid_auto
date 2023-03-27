<?php
declare (strict_types = 1);

namespace app\middleware;

use think\facade\Session;

class Admin
{
    public function handle($request, \Closure $next)
    {
        if (!Session::get('user_id')) {
            return response(alert("error", "请先登录", "2000", "/index"));
        } else {
            if (!Session::get('admin'))
                return response(alert("error", "您不是管理员", "2000", "/user/index"));
            return $next($request);
        }
    }
}

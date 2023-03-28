<?php
declare (strict_types = 1);

namespace app\middleware;

use app\model\User;
use think\facade\Session;
use Closure;

class Admin
{
    public function handle($request, Closure $next)
    {
        if (!Session::get('user_id')) {
            return response(alert("error", "请先登录", "2000", "/index"));
        } else {
            $user = new User();
            $user = $user -> fetch(Session::get('user_id'));
            if ($user -> is_admin == 0) {
                return response(alert("error", "您没有权限访问该页面", "2000", "/index"));
            }
            return $next($request);
        }
    }
}

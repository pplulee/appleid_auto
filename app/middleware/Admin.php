<?php
declare (strict_types=1);

namespace app\middleware;

use app\model\User;
use Closure;
use think\facade\Session;

class Admin
{
    public function handle($request, Closure $next)
    {
        if (!Session::get('user_id')) {
            return response(alert("error", "请先登录", "2000", "/index"));
        } else {
            $user = new User();
            $user = $user->fetch(Session::get('user_id'));
            if (!$user->is_admin) {
                return response(alert("error", "您没有权限访问该页面", "2000", "/user"));
            }
            return $next($request);
        }
    }
}

<?php
declare (strict_types = 1);

namespace app\middleware;

use Closure;
use think\facade\Session;

class UserIndex
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return \think\Response
     */
    public function handle($request, Closure $next)
    {
        if (!Session::get('user_id')) {
            return response(alert("error", "请先登录", "2000", "/index"));
        } else {
            return $next($request);
        }
    }
}

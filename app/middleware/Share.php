<?php
declare (strict_types=1);

namespace app\middleware;

use app\model\SharePage;
use Closure;
use think\Response;

class Share
{
    public function handle($request, Closure $next)
    {
        $shareLink = $request->param('link');
        if (!$shareLink) {
            return Response::create("<h1>分享链接未设置</h1>");
        }
        $share = new SharePage();
        $share= $share->fetchByLink($shareLink);
        if (!$share) {
            return Response::create("<h1>分享链接不存在</h1>");
        }
        if ($share->password) {
            if (!$request->param('password')) {
                return view('share/password', ['link' => $shareLink]);
            }
            if ($request->param('password') != $share->password) {
                return Response::create("<h1>密码错误</h1>");
            }
        }
        if ($share->expire_time!=null && $share->expire_time < time()) {
            return Response::create("<h1>分享链接已过期</h1>");
        }
        $request->share = $share;
        return $next($request);
    }
}

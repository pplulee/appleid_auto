<?php
declare (strict_types=1);

namespace app\middleware;

use app\model\SharePage;
use Closure;

class Share
{
    public function handle($request, Closure $next)
    {
        $shareLink = $request->param('link');
        if (!$shareLink) {
            return view('share/error', ['errorTitle' => '页面不存在', 'errorMsg' => '此分享链接不存在']);
        }
        if ($request->param('id')){
            // 触发手动解锁
            $request->id = $request->param('id');
            $request->link = $shareLink;
            return $next($request);
        }
        $share = new SharePage();
        $share = $share->fetchByLink($shareLink);
        if (!$share) {
            return view('share/error', ['errorTitle' => '页面不存在', 'errorMsg' => '此分享链接不存在']);
        }
        if ($share->password) {
            if (!$request->param('password')) {
                return view('share/password', ['link' => $shareLink]);
            }
            if ($request->param('password') != $share->password) {
                return view('share/error', ['errorTitle' => '密码错误', 'errorMsg' => '分享链接密码错误']);
            }
        }
        if ($share->expire_time != null && $share->expire_time < time()) {
            return view('share/error', ['errorTitle' => '页面已过期', 'errorMsg' => '此分享链接已过期']);
        }
        $request->share = $share;
        return $next($request);
    }
}

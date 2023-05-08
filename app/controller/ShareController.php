<?php
declare (strict_types=1);

namespace app\controller;

use app\BaseController;
use think\Request;
use think\response\View;

class ShareController extends BaseController
{
    public function index(Request $request): View
    {
        $share = $request->share;
        if (!$share) {
            return view('share/error', ['errorTitle' => '操作有误', 'errorMsg' => '非法请求']);
        }
        $accountList = $share->account_list;
        $accounts = [];
        foreach ($accountList as $accountID) {
            $account = $this->app->accountService->fetchInShare($accountID);
            if (!$account) {
                continue;
            } else {
                $account->status = $account->message == "正常" && ((time() - strtotime($account->last_check)) < (($account->check_interval + 2) * 60));
                $accounts[] = $account;
            }
        }
        // 账号随机排序
        if (env("share_random")) {
            shuffle($accounts);
        }
        return view('share/result', ['accounts' => $accounts, 'html' => $share->html, 'link' => $share->share_link]);
    }

    public function manualUnlock(Request $request): string
    {
        $id = $request->id;
        $link = $request->link;
        $result = $this->app->unlockService->unlock($id);
        if ($result['status']) {
            return alert('success', '任务已提交，稍后将会自动解锁', 2000, '/share/' . $link);
        } else {
            return alert('error', $result['msg'], 2000, '/share/' . $link);
        }
    }
}

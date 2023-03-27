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
                $accounts[] = $account;
            }
        }
        return view('share/result', ['accounts' => $accounts, 'html' => $share->html]);
    }
}

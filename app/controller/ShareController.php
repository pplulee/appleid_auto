<?php
declare (strict_types = 1);

namespace app\controller;

use app\model\Account;
use think\Request;

class ShareController
{
    public function index(Request $request)
    {
        $share = $request->share;
        if (!$share) {
            return "操作有误";
        }
        $accountList = $share->account_list;
        $accounts=[];
        foreach ($accountList as $accountID) {
            $account = new Account();
            $account = $account->fetch($accountID);
            if (!$account) {
                continue;
            } else {
                $accounts[] = $account;
            }
        }
        return view('share/result', ['accounts' => $accounts, 'html' => $share->html]);
    }
}

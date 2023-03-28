<?php
declare (strict_types=1);

namespace app\service;

use app\model\Account;
use think\facade\Db;
use think\Paginator;
use think\Service;

class AccountService extends Service
{
    public function register(): void
    {
        $this->app->bind('accountService', accountService::class);
    }

    public function fetchByOwner($user_id): Paginator
    {
        return Db::name('account')->where('owner', $user_id)->paginate(25);
    }

    public function fetchIDByOwner($user_id): array
    {
        return Db::name('account')
            ->field('id, username')
            ->where('owner', $user_id)
            ->column('username', 'id');
    }

    public function fetchAll(): Paginator
    {
        return Db::name('account')->paginate(25);
    }

    public function countAll($id = 0): int
    {
        if ($id == 0) {
            return Db::name('account')->count();
        } else {
            return Db::name('account')->where('owner', $id)->count();
        }
    }

    public function fetchInShare($id): ?Account
    {
        $result = Db::name('account')
            ->field('id,username,password,frontend_remark,last_check,check_interval,message,min_manual_unlock')
            ->where('id', $id)
            ->find();
        if (count($result) == 0) {
            return null;
        } else {
            $account = new Account();
            $account->id = $result['id'];
            $account->username = $result['username'];
            $account->password = $result['password'];
            $account->frontend_remark = $result['frontend_remark'];
            $account->message = $result['message'];
            $account->last_check = $result['last_check'];
            $account->check_interval = $result['check_interval'];
            $account->min_manual_unlock = $result['min_manual_unlock'];
            return $account;
        }
    }

}

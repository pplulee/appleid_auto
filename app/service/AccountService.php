<?php
declare (strict_types=1);

namespace app\service;

use app\model\User;
use think\facade\Db;
use think\Paginator;
use think\Service;

class AccountService extends Service
{
    /**
     * 注册服务
     *
     * @return mixed
     */
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

    public function countAll($id = 0): int
    {
        if ($id == 0) {
            return Db::name('account')->count();
        } else {
            return Db::name('account')->where('owner', $id)->count();
        }
    }

    public function fetchInShare($id): ?User
    {
        $result = Db::name('account')
            ->field('id,username,password,remark,last_check,check_interval')
            ->where('id', $id)
            ->select();
        if (count($result) == 0) {
            return null;
        } else {
            $user = new User();
            $user->id = $result[0]['id'];
            $user->username = $result[0]['username'];
            $user->password = $result[0]['password'];
            $user->remark = $result[0]['remark'];
            $user->last_check = $result[0]['last_check'];
            $user->check_interval = $result[0]['check_interval'];
            return $user;
        }
    }

}

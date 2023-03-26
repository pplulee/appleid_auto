<?php
declare (strict_types=1);

namespace app\service;

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

}

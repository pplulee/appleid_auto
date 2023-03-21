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

}

<?php
declare (strict_types = 1);

namespace app\service;

use think\facade\Db;
use think\Paginator;
use think\Service;

class ShareService extends Service
{
    public function register()
    {
        $this->app->bind('shareService', shareService::class);
    }

    public function fetchByOwner($user_id): Paginator
    {
        return Db::name('share')->where('owner', $user_id)->paginate(25);
    }
}

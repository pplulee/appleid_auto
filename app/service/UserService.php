<?php
declare (strict_types=1);

namespace app\service;

use think\facade\Db;
use think\Paginator;
use think\Service;

class UserService extends Service
{
    /**
     * 注册服务
     *
     * @return mixed
     */
    public function register(): void
    {
        $this->app->bind('userService', userService::class);
    }

    public function countAll($id = 0): int
    {
        if ($id == 0) {
            return Db::name('user')->count();
        } else {
            return Db::name('user')->where('owner', $id)->count();
        }
    }

    public function fetchAll(): Paginator
    {
        return Db::name('user')->paginate(25);
    }

    public function getNotifyMethods($id): array
    {
        $result = Db::name('user')
            ->field('tg_bot_token,tg_chat_id,wx_pusher_id,webhook')
            ->where('id', $id)
            ->find();
        if (empty($result)) {
            return [];
        } else {
            return $result;
        }
    }

}

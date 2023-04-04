<?php
declare (strict_types=1);

namespace app\service;

use app\model\Proxy;
use think\facade\Db;
use think\Paginator;
use think\Service;

class ProxyService extends Service
{
    public function register()
    {
        $this->app->bind('proxyService', ProxyService::class);
    }

    public function getProtocolList(): array
    {
        return ['http', 'socks5', 'http+url', 'socks5+url'];
    }

    public function fetchByOwner($user_id): Paginator
    {
        return Db::name('proxy')->where('owner', $user_id)->paginate(25);
    }

    public function fetchAll(): Paginator
    {
        return Db::name('proxy')->paginate(25);
    }

    public function getAvailableProxy($userID): array
    {
        $result = Db::name('proxy')
            ->field('id,protocol,content')
            ->where('status', 1)
            ->where('owner', $userID)
            ->orderRand()
            ->find();
        if (!$result) {
            return [];
        } else {
            $proxy = new Proxy();
            $proxy->update(['last_use' => date('Y-m-d H:i:s')], ['id' => $result['id']]);
            return $result;
        }
    }
}

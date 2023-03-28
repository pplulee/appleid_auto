<?php
declare (strict_types=1);

namespace app\service;

use think\console\Output;
use think\facade\Db;
use think\Paginator;
use think\Service;

class UnlockService extends Service
{
    public function register()
    {
        $this->app->bind('unlockService', UnlockService::class);
    }

    public function unlock($id)
    {
        if (!env("enable_backend_api")) {
            return ['status' => false, 'msg' => '后端API未启用'];
        }
        $account = Db::name('account')
            ->field('min_manual_unlock')
            ->where('id', $id)
            ->find();
        if (count($account) == 0) {
            return ['status' => false, 'msg' => '账号不存在'];
        }
        $min_manual_unlock = $account['min_manual_unlock'];
        if ($min_manual_unlock == 0) {
            return ['status' => false, 'msg' => '该账号未启用手动解锁'];
        }
        $timeAgo = date('Y-m-d H:i:s', time() - $min_manual_unlock * 60);
        $result = Db::name('unlock_record')
            ->where('account_id', $id)
            ->where('status', 1)
            ->where('type', 'manual')
            ->where('time', '>', $timeAgo)
            ->count();
        if ($result > 0) {
            return ['status' => false, 'msg' => '间隔太短，暂不允许手动解锁'];
        } else {
            $backendResult = $this->app->backendService->restartTask($id);
            Db::name('unlock_record')->insert([
                'account_id' => $id, 'type' => 'manual',
                'status' => $backendResult['status'],
                'message' => $backendResult['msg'],
                'ip' => getUserIP()
            ]);
            $output = new Output();
            $output->writeln('手动解锁：' . $backendResult['msg']);
            return $backendResult;
        }
    }

    public function fetchRecord($userID = 0): Paginator
    {
        return Db::name('unlock_record')
            ->join('account', 'unlock_record.account_id=account.id')
            ->field('unlock_record.*,account.username')
            ->order('unlock_record.id desc')
            ->where('account.owner', 'LIKE', $userID == 0 ? "%" : $userID)
            ->paginate(10);
    }
}

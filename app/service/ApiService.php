<?php
declare (strict_types=1);

namespace app\service;

use app\model\Account;
use app\model\UnlockRecord;
use think\facade\Db;
use think\Service;

class ApiService extends Service
{
    public function register(): void
    {
        $this->app->bind('apiService', ApiService::class);
    }

    public function updateAccount($username, $password, $status, $message): bool
    {
        $account = new Account();
        $account = $account->fetchByUsername($username);
        if (!$account) {
            return false;
        }
        if ($password != null) {
            $account->password = $password;
        }
        $account->message = $message;
        $account->last_check = date('Y-m-d H:i:s');
        $record = new UnlockRecord();
        $record->addRecord([
            'account_id' => $account->id,
            'status' => $status,
            'type' => 'backend',
            'message' => $message
        ]);
        return $account->save();
    }

    public function getPassword($username): ?string
    {
        $account = new Account();
        $account = $account->fetchByUsername($username);
        if (!$account) {
            return null;
        }
        return $account->password;
    }

}

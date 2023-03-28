<?php
declare (strict_types=1);

namespace app\model;

use think\facade\Db;
use think\Model;

/**
 * @mixin Model
 */
class Account extends Model
{
    protected $table = 'account';
    protected $pk = 'id';

    function addAccount($data): bool
    {
        if ($this->fetchByUsername($data['username'])) {
            return false;
        }
        $account = new Account();
        $data['message'] = "未执行任务";
        $account->create($data);
        return true;
    }

    function fetchByUsername($username)
    {
        return $this->where('username', $username)->find();
    }

    function deleteAccount($id): bool
    {
        if (!$this) {
            $account = $this->fetch($id);
        } else {
            $account = $this;
        }
        if (!$account) {
            return false;
        }
        $pages = Db::table('share')
            ->field('id, account_list')
            ->where('locate(:id, account_list)', ['id' => $id])
            ->column('id, account_list');
        foreach ($pages as $page) {
            $account_list = array_map('intval', explode(",", $page['account_list']));
            if (count($account_list) == 1) {
                Db::table('share')
                    ->where('id', $page['id'])
                    ->delete();
                continue;
            }
            $account_list = array_diff($account_list, [$id]);
            $account_list = implode(",", $account_list);
            Db::table('share')
                ->where('id', $page['id'])
                ->update(['account_list' => $account_list]);
        }
        return $account->delete();
    }

    function fetch($id)
    {
        return $this->where('id', $id)->find();
    }

    function updateAccount($id, $data): bool
    {
        if (!$this) {
            $account = $this->fetch($id);
        } else {
            $account = $this;
        }
        if (!$account) {
            return false;
        }
        $account->update($data, ['id' => $id]);
        return true;
    }

    function getTaskList(): array
    {
        return $this->column('id');
    }
}

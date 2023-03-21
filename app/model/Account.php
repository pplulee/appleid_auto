<?php
declare (strict_types=1);

namespace app\model;

use think\facade\Db;
use think\Model;
use think\Paginator;

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
        $account->create($data);
        return true;
    }

    function fetchByUsername($username)
    {
        return $this->where('username', $username)->find();
    }

    function deleteAccount($id): bool
    {
        $account = $this->fetch($id);
        if (!$account) {
            return false;
        }
        // TODO 删除关联的分享页
        $account->delete();
        return true;
    }

    function updateAccount($data): bool
    {
        $account = $this->fetch($data['id']);
        if (!$account) {
            return false;
        }
        $account->update($data,['id' => $data['id']]);
        return true;
    }

    function fetch($id)
    {
        return $this->where('id', $id)->find();
    }
}

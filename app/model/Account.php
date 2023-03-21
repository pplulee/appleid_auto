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

    function fetchByUserId($user_id): Paginator
    {
        return Db::name('account')->where('owner', $user_id)->paginate(25);
    }

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

    function fetch($id)
    {
        return $this->where('id', $id)->find();
    }
}

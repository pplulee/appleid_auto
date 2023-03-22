<?php
declare (strict_types=1);

namespace app\model;

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
        // TODO 删除关联的分享页
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
}

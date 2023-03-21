<?php
declare (strict_types=1);

namespace app\model;

use think\Model;

/**
 * @mixin Model
 */
class User extends Model
{
    protected $table = 'user';
    protected $pk = 'id';

    public function addUser($username, $password): bool
    {
        $user = $this->where('username', $username)->find();
        if ($user) {
            return false;
        }
        $user = new User();
        $password = password_hash($password, PASSWORD_DEFAULT);
        $user->create(['username' => $username, 'password' => $password]);
        return true;
    }

    public function updateUser($data): bool
    {
        $id = $data['id'];
        $username = $data['username'];
        $password = $data['password'];
        // 如果已经设置信息，则不再查询数据库
        if (!$this) {
            $user = new User();
            $user = $user->fetch($id);
        } else {
            $user = $this;
        }
        if (!$user) {
            return false;
        } else {
            $update = [];
            if ($password != null) {
                $password = password_hash($password, PASSWORD_DEFAULT);
                $update['password'] = $password;
            }
            if ($username != $user->username) {
                // 检查用户名是否重复
                if ($this->where('username', $username)->find()) {
                    return false;
                }
                $update['username'] = $username;
            }
            if (isset($data['is_admin']) && $user->is_admin != $data['is_admin']) {
                $update['is_admin'] = $data['is_admin'];
            }
            if (count($update) > 0) {
                $user->update($update, ['id' => $id]);
            }
            return true;
        }
    }

    public function fetch($id)
    {
        return $this->where('id', $id)->find();
    }
}

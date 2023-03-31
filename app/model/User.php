<?php
declare (strict_types=1);

namespace app\model;

use think\facade\Db;
use think\Model;

/**
 * @mixin Model
 */
class User extends Model
{
    protected $table = 'user';
    protected $pk = 'id';

    public function addUser($username, $password): array
    {
        $user = $this->where('username', $username)->find();
        if ($user) {
            return ['status' => false, 'msg' => '用户名已存在'];
        }
        $user = new User();
        $password = password_hash($password, PASSWORD_DEFAULT);
        $user->create(['username' => $username, 'password' => $password]);
        return ['status' => true, 'msg' => '注册成功'];
    }

    public function updateUser($data): array
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
            return ['status' => false, 'msg' => '用户不存在'];
        } else {
            $update = [];
            if ($password != null) {
                $password = password_hash($password, PASSWORD_DEFAULT);
                $update['password'] = $password;
            }
            if ($username != $user->username) {
                // 检查用户名是否重复
                if ($this->where('username', $username)->find()) {
                    return ['status' => false, 'msg' => '用户名已存在'];
                }
                $update['username'] = $username;
            }
            if (isset($data['is_admin']) && $user->is_admin != $data['is_admin']) {
                $update['is_admin'] = $data['is_admin'];
            }
            $update['tg_bot_token'] = $data['tg_bot_token'];
            $update['tg_chat_id'] = $data['tg_chat_id'];
            $update['wx_pusher_id'] = $data['wx_pusher_id'];
            $update['webhook'] = $data['webhook'];
            if (count($update) > 0) {
                $user->update($update, ['id' => $id]);
            }
            return ['status' => true, 'msg' => '更新成功'];
        }
    }

    public function fetch($id)
    {
        return $this->where('id', $id)->find();
    }

    public function deleteUser($id): array
    {
        $user = $this->fetch($id);
        if (!$user) {
            return ['status' => false, 'msg' => '用户不存在'];
        } else {
            // 删除代理
            Db::name('proxy')->where('owner', $id)->delete();
            // 删除分享页
            Db::name('share')->where('owner', $id)->delete();
            // 删除账号
            Db::name('account')->where('owner', $id)->delete();
            $result = $user->delete();
            return ['status' => $result, 'msg' => $result ? '删除成功' : '删除失败'];
        }
    }
}

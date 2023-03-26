<?php
declare (strict_types=1);

namespace app\service;

use app\model\User;
use think\facade\Db;
use think\facade\Session;
use think\Service;

class AuthService extends Service
{
    public function register(): void
    {
        $this->app->bind('authService', AuthService::class);
    }

    public function userLogin($username, $password): bool
    {
        $user = Db::table('user')->where('username', $username)->find();
        if ($user) {
            if (password_verify($password, $user['password'])) {
                Session::set('user_id', $user['id']);
                return true;
            }
        }
        return false;
    }

    public function userRegister($username, $password): bool
    {
        return (new User)->addUser($username, $password);
    }

    public function isAdmin($user_id): bool
    {
        $user = Db::table('user')->where('id', $user_id)->find();
        if ($user) {
            return $user['is_admin'];
        }
        return false;
    }
}

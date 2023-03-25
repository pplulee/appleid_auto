<?php
declare (strict_types=1);

namespace app\controller;

use app\BaseController;
use app\model\Account;
use app\model\SharePage;
use app\model\User;
use think\console\Output;
use think\facade\Session;
use think\response\Json;
use think\response\View;

class UserController extends BaseController
{
    public function index()
    {
        $user = new User();
        $user = $user->fetch(Session::get('user_id'));
        if (!$user) {
            return alert("error", "用户不存在", "2000", "/index");
        }
        $account_count = 0; // TODO
        $share_count = 0; // TODO
        return view('/user/index', ['user' => $user, 'account_count' => $account_count, 'share_count' => $share_count]);

    }

    public function info()
    {
        $user = new User();
        $user = $user->fetch(Session::get('user_id'));
        if (!$user) {
            return alert("error", "用户不存在", "2000", "/index");
        }
        return view('/user/info', ['user' => $user]);
    }

    public function updateUser(): string
    {
        $user = new User();
        $user = $user->fetch(Session::get('user_id'));
        if (!$user) {
            return alert("error", "用户不存在", "2000", "/index");
        }
        $data = [
            'id' => $user->id,
            'username' => $this->request->post('username'),
            'password' => $this->request->post('password'),
            'tg_bot_token' => $this->request->post('tg_bot_token'),
            'tg_chat_id' => $this->request->post('tg_chat_id'),
            'wx_pusher_id' => $this->request->post('wx_pusher_id'),
        ];
        if ($user->updateUser($data)) {
            return alert("success", "修改成功", "2000", "/user/info");
        } else {
            return alert("error", "修改失败", "2000", "/user/info");
        }
    }

    public function login(): string
    {
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        if ($this->app->authService->userLogin($username, $password)) {
            return alert("success", "登录成功", "2000", "/user/index");
        } else {
            return alert("error", "用户名或密码错误", "2000", "/index");
        }
    }

    public function register(): string
    {
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        if ($this->app->authService->userRegister($username, $password)) {
            return alert("success", "注册成功", "2000", "/index");
        } else {
            return alert("error", "用户已存在", "2000", "/index");
        }
    }

    public function logout(): string
    {
        Session::delete('user_id');
        return alert("success", "登出成功", "2000", "/index");
    }

    public function account(): View
    {
        $accountList = $this->app->accountService->fetchByOwner(Session::get('user_id'));
        return view('/user/account', ['accounts' => $accountList]);
    }

    public function accountEdit($id)
    {
        $account = new Account();
        $account = $account->fetch($id);
        if (!$account) {
            return alert("error", "账号不存在", "2000", "/user/account");
        }
        if ($account->owner != Session::get('user_id')) {
            return alert("error", "无权操作", "2000", "/user/account");
        }
        return view('/user/accountDetail', ['account' => $account, 'action' => 'edit']);
    }

    public function accountAdd(): View
    {
        $account = new Account();
        $account->share_link = random_str(10);
        $account->check_interval = 30;
        return view('/user/accountDetail', ['account' => $account, 'action' => 'add']);
    }

    public function accountUpdate($id = 0): string
    {
        $data = [
            'username' => $this->request->post('username'),
            'password' => $this->request->post('password'),
            'remark' => $this->request->post('remark'),
            'dob' => $this->request->post('dob'),
            'question1' => $this->request->post('question1'),
            'answer1' => $this->request->post('answer1'),
            'question2' => $this->request->post('question2'),
            'answer2' => $this->request->post('answer2'),
            'question3' => $this->request->post('question3'),
            'answer3' => $this->request->post('answer3'),
            'share_link' => $this->request->post('share_link'),
            'check_interval' => $this->request->post('check_interval'),
            'frontend_remark' => $this->request->post('frontend_remark'),
            'enable_check_password_correct' => $this->request->post('enable_check_password_correct') !== null,
            'enable_delete_devices' => $this->request->post('enable_delete_devices') !== null,
            'enable_auto_update_password' => $this->request->post('enable_auto_update_password') !== null,
        ];
        $account = new Account();
        switch ($this->request->post('action')) {
            case "edit":
                $account = $account->fetch($id);
                if (!$account) {
                    return alert("error", "账号不存在", "2000", "/user/account");
                }
                if ($account->owner != Session::get('user_id')) {
                    return alert("error", "无权操作", "2000", "/user/account");
                }
                return $account->updateAccount($account->id, $data) ?
                    alert("success", "修改成功", "2000", "/user/account") :
                    alert("error", "修改失败", "2000", "/user/account");
            case "add":
                $data['owner'] = Session::get('user_id');
                return $account->addAccount($data) ?
                    alert("success", "添加成功", "2000", "/user/account") :
                    alert("error", "添加失败", "2000", "/user/account");
            default:
                return alert("error", "未知操作", "2000", "/user/account");
        }
    }

    public function accountDelete($id): Json
    {
        $account = new Account();
        $result = [];
        $account = $account->fetch($id);
        if (!$account) {
            $result['msg'] = "账号不存在";
            $result['status'] = false;
        } elseif ($account->owner != Session::get('user_id')) {
            $result['msg'] = "无权操作";
            $result['status'] = false;
        } else {
            $result['status'] = $account->deleteAccount($account->id);
            $output = new Output();
            $output->writeln((string)$result['status']);
            $result['msg'] = $result['status'] ? "删除成功" : "删除失败";
        }
        return json($result);
    }

    public function share(): View
    {
        $shareList = $this->app->shareService->fetchByOwner(Session::get('user_id'));
        return view('/user/share', ['shares' => $shareList]);
    }

    public function shareAdd()
    {
        $share = new SharePage();
        $userAccountList = $this->app->accountService->fetchByOwner(Session::get('user_id'));
        // 检查用户是否有账号
        if (count($userAccountList) == 0) {
            return alert("error", "请先添加账号", "2000", "/user/account");
        }
        return view('/user/shareDetail', ['share' => $share, 'accounts' => $userAccountList, 'action' => 'add']);
    }
}

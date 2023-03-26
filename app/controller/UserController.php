<?php
declare (strict_types=1);

namespace app\controller;

use app\BaseController;
use app\model\Account;
use app\model\Proxy;
use app\model\SharePage;
use app\model\User;
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
        $account_count = $this->app->accountService->countAll($user->id);
        $share_count = $this->app->shareService->countAll($user->id);
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
            $result['msg'] = $result['status'] ? "删除成功" : "删除失败";
        }
        return json($result);
    }

    public function share(): View
    {
        $shareList = $this->app->shareService->fetchByOwner(Session::get('user_id'));
        $shareURL = $this->request->domain() . "/share/";
        return view('/user/share', ['shares' => $shareList,'shareURL'=>$shareURL]);
    }

    public function shareAdd()
    {
        $share = new SharePage();
        $userAccountList = $this->app->accountService->fetchIDByOwner(Session::get('user_id'));
        // 检查用户是否有账号
        if (count($userAccountList) == 0) {
            return alert("error", "请先添加账号", "2000", "/user/account");
        }
        $share->share_link = random_str(10);
        $share->account_list = $userAccountList;
        return view('/user/shareDetail', ['share' => $share, 'accounts' => $userAccountList, 'action' => 'add']);
    }

    public function shareEdit($id)
    {
        $share = new SharePage();
        $share = $share->fetch($id);
        if (!$share) {
            return alert("error", "分享页面不存在", "2000", "/user/share");
        }
        if ($share->owner != Session::get('user_id')) {
            return alert("error", "无权操作", "2000", "/user/share");
        }
        $userAccountList = $this->app->accountService->fetchIDByOwner(Session::get('user_id'));
        return view('/user/shareDetail', ['share' => $share, 'accounts' => $userAccountList, 'action' => 'edit']);
    }

    public function shareUpdate($id = 0): string
    {
        $account_list = $this->request->post('account_list');
        if (!$account_list) {
            return alert("error", "请至少选择一个账号", "2000", "/user/share".$id==0?"":"/$id");
        }
        $accounts = implode(',', $account_list);
        $data = [
            'share_link' => $this->request->post('share_link'),
            'account_list' => $accounts,
            'owner' => Session::get('user_id'),
            'html' => $this->request->post('html'),
            'remark' => $this->request->post('remark'),
            'expire' => $this->request->post('expire')==""?null:$this->request->post('expire'),
        ];
        $sharePage = new SharePage();
        switch ($this->request->post('action')) {
            case "edit":
                $sharePage = $sharePage->fetch($id);
                if (!$sharePage) {
                    return alert("error", "分享页面不存在", "2000", "/user/share");
                }
                if ($sharePage->owner != Session::get('user_id')) {
                    return alert("error", "无权操作", "2000", "/user/share");
                }
                return $sharePage->updateSharePage($sharePage->id, $data) ?
                    alert("success", "修改成功", "2000", "/user/share") :
                    alert("error", "修改失败", "2000", "/user/share");
            case "add":
                return $sharePage->addSharePage($data) ?
                    alert("success", "添加成功", "2000", "/user/share") :
                    alert("error", "添加失败", "2000", "/user/share");
            default:
                return alert("error", "未知操作", "2000", "/user/share");
        }
    }

    public function shareDelete($id): Json
    {
        $sharePage = new SharePage();
        $result = [];
        $sharePage = $sharePage->fetch($id);
        if (!$sharePage) {
            $result['msg'] = "分享页面不存在";
            $result['status'] = false;
        } elseif ($sharePage->owner != Session::get('user_id')) {
            $result['msg'] = "无权操作";
            $result['status'] = false;
        } else {
            $result['status'] = $sharePage->deleteSharePage($sharePage->id);
            $result['msg'] = $result['status'] ? "删除成功" : "删除失败";
        }
        return json($result);
    }

    public function proxy(): View
    {
        $proxyList = $this->app->proxyService->fetchByOwner(Session::get('user_id'));
        return view('/user/proxy', ['proxies' => $proxyList]);
    }

    public function proxyAdd(): View
    {
        $proxy = new Proxy();
        $protocols = $this->app->proxyService->getProtocolList();
        return view('/user/proxyDetail', ['proxy' => $proxy, 'action' => 'add', 'protocols' => $protocols]);
    }

    public function proxyEdit($id)
    {
        $proxy = new Proxy();
        $proxy = $proxy->fetch($id);
        if (!$proxy) {
            return alert("error", "代理不存在", "2000", "/user/proxy");
        }
        if ($proxy->owner != Session::get('user_id')) {
            return alert("error", "无权操作", "2000", "/user/proxy");
        }
        $protocols = $this->app->proxyService->getProtocolList();
        return view('/user/proxyDetail', ['proxy' => $proxy, 'action' => 'edit', 'protocols' => $protocols]);
    }

    public function proxyUpdate($id=0): string
    {
        $data = [
            'protocol' => $this->request->post('protocol'),
            'content' => $this->request->post('content'),
            'status' => $this->request->post('status')!==null,
            'owner' => Session::get('user_id'),
        ];
        $proxy = new Proxy();
        switch ($this->request->post('action')) {
            case "edit":
                $proxy = $proxy->fetch($id);
                if (!$proxy) {
                    return alert("error", "代理不存在", "2000", "/user/proxy");
                }
                if ($proxy->owner != Session::get('user_id')) {
                    return alert("error", "无权操作", "2000", "/user/proxy");
                }
                return $proxy->updateProxy($proxy->id, $data) ?
                    alert("success", "修改成功", "2000", "/user/proxy") :
                    alert("error", "修改失败", "2000", "/user/proxy");
            case "add":
                return $proxy->addProxy($data) ?
                    alert("success", "添加成功", "2000", "/user/proxy") :
                    alert("error", "添加失败", "2000", "/user/proxy");
            default:
                return alert("error", "未知操作", "2000", "/user/proxy");
        }
    }

    public function proxyDelete($id): Json
    {
        $proxy = new Proxy();
        $result = [];
        $proxy = $proxy->fetch($id);
        if (!$proxy) {
            $result['msg'] = "代理不存在";
            $result['status'] = false;
        } elseif ($proxy->owner != Session::get('user_id')) {
            $result['msg'] = "无权操作";
            $result['status'] = false;
        } else {
            $result['status'] = $proxy->deleteProxy($proxy->id);
            $result['msg'] = $result['status'] ? "删除成功" : "删除失败";
        }
        return json($result);
    }
}

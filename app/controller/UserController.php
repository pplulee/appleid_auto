<?php
declare (strict_types=1);

namespace app\controller;

use app\BaseController;
use app\model\Account;
use app\model\Proxy;
use app\model\SharePage;
use app\model\User;
use Exception;
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
        try {
            $data = [
                'id' => $user->id,
                'username' => $this->request->param('username'),
                'password' => $this->request->param('password'),
                'tg_bot_token' => $this->request->param('tg_bot_token'),
                'tg_chat_id' => $this->request->param('tg_chat_id'),
                'wx_pusher_id' => $this->request->param('wx_pusher_id'),
            ];
        } catch (Exception $e) {
            return alert("error", "参数错误", "2000", "/user/info");
        }
        if ($user->updateUser($data)) {
            return alert("success", "修改成功", "2000", "/user/info");
        } else {
            return alert("error", "修改失败", "2000", "/user/info");
        }
    }

    public function login(): string
    {
        $username = $this->request->param('username');
        $password = $this->request->param('password');
        $result = $this->app->authService->userLogin($username, $password);
        if ($result['status']) {
            return alert("success", $result['msg'], "2000", "/user/index");
        } else {
            return alert("error", $result['msg'], "2000", "/index");
        }
    }

    public function register(): string
    {
        $username = $this->request->param('username');
        $password = $this->request->param('password');
        $result = $this->app->authService->userRegister($username, $password);
        return alert($result['status'] ? "success" : "error", $result['msg'], "2000", "/index");
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
        $account->min_manual_unlock = 0;
        $account->enable = true;
        return view('/user/accountDetail', ['account' => $account, 'action' => 'add']);
    }

    public function accountUpdate($id = 0): string
    {
        try {
            $data = [
                'username' => $this->request->param('username'),
                'password' => $this->request->param('password'),
                'remark' => $this->request->param('remark'),
                'dob' => $this->request->param('dob'),
                'question1' => $this->request->param('question1'),
                'answer1' => $this->request->param('answer1'),
                'question2' => $this->request->param('question2'),
                'answer2' => $this->request->param('answer2'),
                'question3' => $this->request->param('question3'),
                'answer3' => $this->request->param('answer3'),
                'share_link' => $this->request->param('share_link'),
                'check_interval' => $this->request->param('check_interval'),
                'frontend_remark' => $this->request->param('frontend_remark'),
                'enable_check_password_correct' => $this->request->param('enable_check_password_correct') !== null,
                'enable_delete_devices' => $this->request->param('enable_delete_devices') !== null,
                'enable_auto_update_password' => $this->request->param('enable_auto_update_password') !== null,
                'min_manual_unlock' => $this->request->param('min_manual_unlock'),
                'enable' => $this->request->param('enable') !== null,
            ];
        } catch (Exception $e) {
            return alert("error", "参数错误", "2000", "/user/account");
        }
        $account = new Account();
        switch ($this->request->param('action')) {
            case "edit":
                $account = $account->fetch($id);
                if (!$account) {
                    return alert("error", "账号不存在", "2000", "/user/account");
                }
                if ($account->owner != Session::get('user_id')) {
                    return alert("error", "无权操作", "2000", "/user/account");
                }
                $result = $account->updateAccount($account->id, $data);
                return alert($result['status'] ? "success" : "error", $result['msg'], "2000", "/user/account");
            case "add":
                $data['owner'] = Session::get('user_id');
                $result = $account->addAccount($data);
                return alert($result['status'] ? "success" : "error", $result['msg'], "2000", "/user/account");
            default:
                return alert("error", "未知操作", "2000", "/user/account");
        }
    }

    public function accountRestart($id): string
    {
        $account = new Account();
        $account = $account->fetch($id);
        if (!$account) {
            return alert("error", "账号不存在", "2000", "/user/account");
        }
        if ($account->owner != Session::get('user_id')) {
            return alert("error", "无权操作", "2000", "/user/account");
        }
        $backendResult = $this->app->backendService->restartTask($id);
        if ($backendResult['status']) {
            return alert("success", "重启成功", "2000", "/user/account");
        } else {
            return alert("error", "重启失败：" . $backendResult['msg'], "2000", "/user/account");
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
            $result = $account->deleteAccount($account->id);
        }
        return json($result);
    }

    public function share(): View
    {
        $shareList = $this->app->shareService->fetchByOwner(Session::get('user_id'));
        $shareURL = $this->request->domain() . "/share/";
        return view('/user/share', ['shares' => $shareList, 'shareURL' => $shareURL]);
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
        try {
            $account_list = $this->request->param('account_list');
            if (!$account_list) {
                return alert("error", "请至少选择一个账号", "2000", "/user/share" . $id == 0 ? "" : "/$id");
            }
            $accounts = implode(',', $account_list);
            $data = [
                'share_link' => $this->request->param('share_link'),
                'account_list' => $accounts,
                'password' => $this->request->param('password') == "" ? null : $this->request->param('password'),
                'owner' => Session::get('user_id'),
                'html' => $this->request->param('html'),
                'remark' => $this->request->param('remark'),
                'expire' => $this->request->param('expire') == "" ? null : $this->request->param('expire'),
            ];
        } catch (Exception $e) {
            return alert("error", "参数错误", "2000", "/user/share" . $id == 0 ? "" : "/$id");
        }
        $sharePage = new SharePage();
        switch ($this->request->param('action')) {
            case "edit":
                $sharePage = $sharePage->fetch($id);
                if (!$sharePage) {
                    return alert("error", "分享页面不存在", "2000", "/user/share");
                }
                if ($sharePage->owner != Session::get('user_id')) {
                    return alert("error", "无权操作", "2000", "/user/share");
                }
                $result = $sharePage->updateSharePage($sharePage->id, $data);
                return alert($result['status'] ? "success" : "error", $result['msg'], "2000", "/user/share");
            case "add":
                $result = $sharePage->addSharePage($data);
                return alert($result['status'] ? "success" : "error", $result['msg'], "2000", "/user/share");
            default:
                return alert("error", "未知操作", "2000", "/user/share");
        }
    }

    public function shareDelete($id): Json
    {
        $sharePage = new SharePage();
        $sharePage = $sharePage->fetch($id);
        if (!$sharePage) {
            $result['msg'] = "分享页面不存在";
            $result['status'] = false;
        } elseif ($sharePage->owner != Session::get('user_id')) {
            $result['msg'] = "无权操作";
            $result['status'] = false;
        } else {
            $result = $sharePage->deleteSharePage($sharePage->id);
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

    public function proxyUpdate($id = 0): string
    {
        try {
            $data = [
                'protocol' => $this->request->param('protocol'),
                'content' => $this->request->param('content'),
                'status' => $this->request->param('status') !== null,
                'owner' => Session::get('user_id'),
            ];
        } catch (Exception $e) {
            return alert("error", "参数错误", "2000", "/user/proxy" . $id == 0 ? "" : "/$id");
        }
        $proxy = new Proxy();
        switch ($this->request->param('action')) {
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

    public function unlockRecord(): View
    {
        $unlockRecordList = $this->app->unlockService->fetchRecord(Session::get('user_id'));
        return view('/user/unlockRecord', ['unlockRecords' => $unlockRecordList]);
    }
}

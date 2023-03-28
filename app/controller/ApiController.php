<?php
declare (strict_types=1);

namespace app\controller;

use app\BaseController;
use app\model\Account;
use app\model\Proxy;
use app\model\SharePage;
use think\response\Json;

class ApiController extends BaseController
{
    public function getTaskList(): Json
    {
        $account = new Account();
        $task_list = $account->getTaskList();
        return json(['code' => 200, 'msg' => '获取成功', 'status' => true, 'data' => $task_list]);
    }

    public function getTaskInfo(): Json
    {
        if (!$this->request->param('id')) {
            return json(['code' => 400, 'msg' => '缺少TaskID']);
        }
        $account = new Account();
        $account_info = $account->fetch($this->request->param('id'));
        if (!$account_info) {
            return json(['code' => 404, 'status' => false, 'msg' => 'TaskID不存在']);
        }
        $data = array(
            'username' => $account_info->username,
            'password' => $account_info->password,
            'dob' => $account_info->dob,
            'q1' => $account_info->question1,
            'q2' => $account_info->question2,
            'q3' => $account_info->question3,
            'a1' => $account_info->answer1,
            'a2' => $account_info->answer2,
            'a3' => $account_info->answer3,
            'check_interval' => $account_info->check_interval,
            'webdriver' => env('WEBDRIVER', ''),
        );
        // 获取可选参数
        if ($account_info->enable_check_password_correct) {
            $data['check_password_correct'] = true;
        }
        if ($account_info->enable_delete_devices) {
            $data['enable_delete_devices'] = true;
        }
        if ($account_info->enable_auto_update_password) {
            $data['enable_auto_update_password'] = true;
        }
        if (env('task_headless', false)) {
            $data['task_headless'] = true;
        }
        // 处理通知参数
        $notify_params = $this->app->userService->getNotifyMethods($account_info->owner);
        if (!empty($notify_params)) {
            if ($notify_params['tg_bot_token'] != "") {
                $data['tg_bot_token'] = $notify_params['tg_bot_token'];
            }
            if ($notify_params['tg_chat_id'] != "") {
                $data['tg_chat_id'] = $notify_params['tg_chat_id'];
            }
            if ($notify_params['wx_pusher_id'] != "") {
                $data['wx_pusher_id'] = $notify_params['wx_pusher_id'];
            }
            if ($notify_params['webhook'] != "") {
                $data['webhook'] = $notify_params['webhook'];
            }
        }
        // 处理代理参数
        if (env('enable_proxy_pool', false)) {
            $proxy = $this->app->proxyService->getAvailableProxy($account_info->owner);
            if ($proxy) {
                $data['proxy_id'] = $proxy['id'];
                $data['proxy_protocol'] = $proxy['protocol'];
                $data['proxy_content'] = $proxy['content'];
            }
        }
        return json(['code' => 200, 'msg' => '获取成功', 'status' => true, 'data' => $data]);
    }

    public function updateAccount(): json
    {
        if (!$this->request->param('username')
            || !$this->request->param('status')
            || !$this->request->param('message')) {
            return json(['code' => 400, 'msg' => '缺少参数', 'status' => false]);
        }
        $result = $this->app->apiService->updateAccount(
            $this->request->param('username'),
            $this->request->param('password'),
            $this->request->param('status') == 'True',
            $this->request->param('message')
        );
        if ($result) {
            return json(['code' => 200, 'msg' => '更新成功', 'status' => true]);
        } else {
            return json(['code' => 500, 'msg' => '更新失败', 'status' => false]);
        }
    }

    public function getPassword(): json
    {
        if (!$this->request->param('username')) {
            return json(['code' => 400, 'msg' => '缺少用户名', 'status' => false]);
        }
        $password = $this->app->apiService->getPassword($this->request->param('username'));
        if (!$password) {
            return json(['code' => 404, 'msg' => '用户不存在', 'status' => false]);
        }
        return json(['code' => 200, 'msg' => '获取成功', 'status' => true, 'data' => ['password' => $password]]);
    }

    public function checkApi(): json
    {
        return json(['code' => 200, 'msg' => 'API正常', 'version' => 2]);
    }

    public function randomSharePagePassword(): json
    {
        $id = $this->request->param('id');
        if (!$id) {
            return json(['code' => 400, 'msg' => '缺少分享页ID', 'status' => false]);
        }
        $share_page = new SharePage();
        $share_page = $share_page->fetch($id);
        if (!$share_page) {
            return json(['code' => 404, 'msg' => '分享页不存在', 'status' => false]);
        }
        $share_page->password = random_str(8);
        if ($share_page->save()) {
            return json(['code' => 200, 'msg' => '更新成功', 'status' => true, 'data' => ['password' => $share_page->password]]);
        } else {
            return json(['code' => 500, 'msg' => '更新失败', 'status' => false]);
        }
    }

    public function reportProxyError(): json
    {
        if (!env('PROXY_AUTO_DISABLE') || !env('ENABLE_PROXY_POOL')) {
            return json(['code' => 406, 'msg' => '代理池未启用或代理自动禁用', 'status' => false]);
        }
        $id = $this->request->param('id');
        if (!$id) {
            return json(['code' => 400, 'msg' => '缺少代理ID', 'status' => false]);
        }
        $proxy = new Proxy();
        $proxy_info = $proxy->fetch($id);
        if (!$proxy_info) {
            return json(['code' => 404, 'msg' => '代理不存在', 'status' => false]);
        }
        if ($proxy_info->updateUse($id) && $proxy_info->setDisable($id)) {
            return json(['code' => 200, 'msg' => '报告成功', 'status' => true]);
        }
        return json(['code' => 500, 'msg' => '报告失败', 'status' => false]);
    }

    public function getBackendApi(): json
    {
        $data['enable'] = env('backend.enable_api');
        if ($data['enable']) {
            $data['listen_ip'] = env('backend.listen_ip');
            $data['listen_port'] = env('backend.listen_port');
            $data['token'] = env('backend.token');
        }
        return json(['code' => 200, 'msg' => '获取成功', 'status' => true, 'data' => $data]);
    }
}
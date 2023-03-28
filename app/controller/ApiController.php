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

class ApiController extends BaseController
{
    public function getTaskList(): Json {
        $account = new Account();
        $task_list = $account->getTaskList();
        return json(['code' => 200, 'msg' => '获取成功', 'data' => $task_list]);
    }

    public function getTaskInfo(): Json {
        if (!$this->request->post('id')) {
            return json(['code' => 400, 'msg' => '缺少TaskID']);
        }
        $account = new Account();
        $account_info = $account->fetch($this->request->post('id'));
        if (!$account_info) {
            return json(['code' => 404, 'msg' => 'TaskID不存在']);
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
//                    'check_interval' => $account_info->check_interval,
//                    'tgbot_token' => $account_info->tgbot_token,
//                    'tgbot_chatid' => $account_info->tgbot_chatid,
                    'API_key' => env('API_KEY'),
                    'webdriver' => env('WEBDRIVER'),
                );
        return json(['code' => 200, 'msg' => '获取成功', 'data' => $data]);
    }

    public function updateMessage(): json {
        if (!$this->request->post('username')) {
            return json(['code' => 400, 'msg' => '缺少用户名']);
        }
        if (!$this->request->post('message')) {
            return json(['code' => 400, 'msg' => '缺少消息']);
        }
        $account = new Account();
        $account_info = $account->fetchByUsername($this->request->post('username'));
        if (!$account_info) {
            return json(['code' => 404, 'msg' => '用户不存在']);
        }
        $account_info->message = $this->request->post('message');
        $account_info->save();
        return json(['code' => 200, 'msg' => '更新成功']);
    }

    public function getPassword(): json {
        if (!$this->request->post('username')) {
            return json(['code' => 400, 'msg' => '缺少用户名']);
        }
        $account = new Account();
        $account_info = $account->fetchByUsername($this->request->post('username'));
        if (!$account_info) {
            return json(['code' => 404, 'msg' => '用户不存在']);
        }
        return json(['code' => 200, 'msg' => '获取成功', 'data' => ['password' => $account_info->password]]);
    }

    public function updatePassword(): json {
        if (!$this->request->post('username')) {
            return json(['code' => 400, 'msg' => '缺少用户名']);
        }
        if (!$this->request->post('password')) {
            return json(['code' => 400, 'msg' => '缺少密码']);
        }
        $account = new Account();
        $account_info = $account->fetchByUsername($this->request->post('username'));
        if (!$account_info) {
            return json(['code' => 404, 'msg' => '用户不存在']);
        }
        $account_info->password = $this->request->post('password');
        $account_info->save();
        return json(['code' => 200, 'msg' => '更新成功']);
    }

    public function checkApi(): json {
        return json(['code' => 200, 'msg' => 'API正常']);
    }

//    public function randomSharePagePassword():json {
//
//    }

    public function reportProxyError():json {
        if (!env(PROXY_AUTO_DISABLE) || !env(ENABLE_PROXY_POOL)) {
            return json(['code' => 406, 'msg' => '代理池未启用或代理自动禁用']);
        }
        $id = $this->request->post('id');
        if (!$id) {
            return json(['code' => 400, 'msg' => '缺少代理ID']);
        }
        $proxy = new Proxy();
        $proxy_info = $proxy->fetch($id);
        if (!$proxy_info) {
            return json(['code' => 404, 'msg' => '代理不存在']);
        }
        if ($proxy_info->updateUse($id) && $proxy_info->setDisable($id)) {
            return json(['code' => 200, 'msg' => '报告成功']);
        }
        return json(['code' => 500, 'msg' => '报告失败！']);
    }
}
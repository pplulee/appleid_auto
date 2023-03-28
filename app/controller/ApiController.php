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

//    public function getTaskInfo(): Json {
//        if (!$this->request->post('id')) {
//            return json(['code' => 400, 'msg' => '缺少TaskID']);
//        }
//        $account = new Account();
//        $account_id = $account->fetch($this->request->post('id'));
//        if (!$account_id) {
//            return json(['code' => 404, 'msg' => 'TaskID不存在']);
//        }
//        $task_info = $account->getTaskInfo($account_id);
//    }
}
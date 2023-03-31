<?php
declare (strict_types=1);

namespace app\model;

use think\Container;
use think\facade\Db;
use think\Model;

/**
 * @mixin Model
 */
class Account extends Model
{
    protected $table = 'account';
    protected $pk = 'id';

    function addAccount($data): array
    {
        if ($this->fetchByUsername($data['username'])) {
            return ['status' => false, 'msg' => '账号已存在'];
        }
        $account = new Account();
        $data['message'] = "未执行任务";
        $account = $account->create($data);
        $backendService = Container::getInstance()->make('backendService');
        $backendResult = $backendService->addTask($account->id);
        $result = [];
        if ($backendResult['status']) {
            $result['status'] = true;
            $result['msg'] = '添加成功';
        } else {
            $result['status'] = true;
            $result['msg'] = '添加成功，但后端接口调用失败：' . $backendResult['msg'];
        }
        return $result;
    }

    function fetchByUsername($username)
    {
        return $this->where('username', $username)->find();
    }

    function deleteAccount($id): array
    {
        if (!$this) {
            $account = $this->fetch($id);
        } else {
            $account = $this;
        }
        if (!$account) {
            return ['status' => false, 'msg' => '账号不存在'];
        }
        $pages = Db::table('share')
            ->field('id, account_list')
            ->where('locate(:id, account_list)', ['id' => $id])
            ->column('id, account_list');
        foreach ($pages as $page) {
            $account_list = array_map('intval', explode(",", $page['account_list']));
            if (count($account_list) == 1) {
                Db::table('share')
                    ->where('id', $page['id'])
                    ->delete();
                continue;
            }
            $account_list = array_diff($account_list, [$id]);
            $account_list = implode(",", $account_list);
            Db::table('share')
                ->where('id', $page['id'])
                ->update(['account_list' => $account_list]);
        }
        $result = [];
        $result['status'] = $account->delete();
        // 通知后端接口
        if ($result['status']) {
            $backendService = Container::getInstance()->make('backendService');
            $backendResult = $backendService->removeTask($id);
            if ($backendResult['status']) {
                $result['msg'] = '删除成功';
            } else {
                $result['status'] = false;
                $result['msg'] = '删除成功，但后端接口错误：' . $backendResult['msg'];
            }
        } else {
            $result['msg'] = '删除失败';
        }
        return $result;
    }

    function fetch($id)
    {
        return $this->where('id', $id)->find();
    }

    function updateAccount($id, $data): array
    {
        $account = $this->fetch($id);
        if (!$account) {
            return ['status' => false, 'msg' => '账号不存在'];
        }
        if ($account->username != $data['username']) {
            if ($this->fetchByUsername($data['username'])) {
                return ['status' => false, 'msg' => '用户名已存在'];
            }
        }
        $result = [];
        $result['status'] = $account->update($data, ['id' => $id]);
        if ($result['status']) {
            $backendService = Container::getInstance()->make('backendService');
            $backendResult = $backendService->restartTask($id, $data);
            if ($backendResult['status']) {
                $result['msg'] = '修改成功';
            } else {
                $result['msg'] = '修改成功，但后端接口错误：' . $backendResult['msg'];
            }
        } else {
            $result['msg'] = '修改失败';
        }
        return $result;
    }

    function getTaskList(): array
    {
        return $this->column('id');
    }
}

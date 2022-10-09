<?php
include($_SERVER['DOCUMENT_ROOT'] . "/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/include/function.php");
include($_SERVER['DOCUMENT_ROOT'] . "/include/user.php");
include($_SERVER['DOCUMENT_ROOT'] . "/include/account.php");
include($_SERVER['DOCUMENT_ROOT'] . "/include/task.php");

global $Sys_config;
$conn = @mysqli_connect($Sys_config["db_host"], $Sys_config["db_user"], $Sys_config["db_password"], $Sys_config["db_database"]);  //数据库连接
if (!$conn) {
    die("数据库连接失败：" . mysqli_connect_error());
}
global $conn;
if (!isset($_GET['key'])) {
    $data = array(
        'status' => 'fail',
        'message' => 'key不能为空'
    );
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
} else if ($_GET['key'] != $Sys_config['apikey']) {
    $data = array(
        'status' => 'fail',
        'message' => 'key错误'
    );
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
switch ($_GET["action"]) {
    case "get_task_list":
    {
        $result = $conn->query("SELECT id FROM task;");
        if ($result->num_rows == 0) {
            $data = array(
                'status' => 'fail',
                'message' => '没有任务'
            );
        } else {
            $task_list = [];
            while ($row = $result->fetch_assoc()) {
                $task_list[] = $row['id'];
            }
            $data = array(
                'status' => 'success',
                'message' => '获取成功',
                'data' => implode(",", $task_list)
            );
        }
        break;
    }
    case "get_task_info":
    {
        if (!isset($_GET['id'])) {
            $data = array(
                'status' => 'fail',
                'message' => 'id不能为空'
            );
        } else {
            $task = new task($_GET['id']);
            if ($task->id == -1) {
                $data = array(
                    'status' => 'fail',
                    'message' => '任务不存在'
                );
            } else {
                $account = new account($task->account_id);
                $data = array(
                    'status' => 'success',
                    'message' => '获取成功',
                    'id_username' => $account->username,
                    'id_dob' => $account->dob,
                    'answer' => $account->question,
                    'check_interval' => $task->check_interval,
                    'tgbot_token' => $task->tgbot_token,
                    'tgbot_chatid' => $task->tgbot_chatid,
                    'API_key' => $Sys_config['apikey'],
                    'API_url' => $Sys_config['apiurl'],
                    'step_sleep' => $Sys_config['backend_step_sleep'],
                    'webdriver' => $Sys_config['webdriver_url']

                );
                break;
            }
        }
        break;
    }
    case "update_password":
    {
        if (!isset($_GET['username']) || !isset($_GET['password'])) {
            $data = array(
                'status' => 'fail',
                'message' => 'ID或密码不能为空'
            );
        } else {
            $account = new account(get_accoubt_id($_GET['username']));
            if ($account->id == -1) {
                $data = array(
                    'status' => 'fail',
                    'message' => '账号不存在'
                );
            } else {
                $account->update_password($_GET['password']);
                $data = array(
                    'status' => 'success',
                    'message' => '更新成功'
                );
            }
        }
        break;
    }
    default:
    {
        $data = array(
            'status' => 'fail',
            'message' => 'action错误'
        );
        break;
    }
}
echo json_encode($data, JSON_UNESCAPED_UNICODE);
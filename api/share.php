<?php
include($_SERVER['DOCUMENT_ROOT'] . "/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/include/function.php");
include($_SERVER['DOCUMENT_ROOT'] . "/include/user.php");
include($_SERVER['DOCUMENT_ROOT'] . "/include/account.php");
include($_SERVER['DOCUMENT_ROOT'] . "/include/sharepage.php");
header('Content-type:text/json');
global $Sys_config;
try {
    $conn = new PDO("mysql:host={$Sys_config["db_host"]};dbname={$Sys_config["db_database"]};", $Sys_config["db_user"], $Sys_config["db_password"]);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // 禁用prepared statements的模拟效果
    $conn->exec("set names utf8"); //设置编码
} catch (PDOException $e) {
    die("数据库连接失败，错误信息：" . $e->getMessage());
}
global $conn;
if (!isset($_GET['share_link'])) {
    $data = array(
        'status' => 'fail',
        'message' => '页面分享代码不能为空'
    );
} else {
    $sharepage = new sharepage(get_share_id($_GET['share_link']));
    if ($sharepage->id == -1) {
        $data = array(
            'status' => 'fail',
            'message' => '分享页面不存在'
        );
    } else {
        if (($_GET['password'] ?? "") != $sharepage->password) {
            $data = array(
                'status' => 'fail',
                'message' => '密码错误'
            );
        } else {
            $account_list = array();
            foreach ($sharepage->account_list as $account_id) {
                $account = new account($account_id);
                $account_list[] = array(
                    'id' => $account->id,
                    'username' => $account->username,
                    'password' => $account->password,
                    'status' => $account->message == "正常" && ((time() - strtotime($account->last_check)) < (($account->check_interval + 2) * 60)),
                    'last_check' => $account->last_check,
                    'remark' => $account->frontend_remark
                );
            }
            $data = array(
                'status' => 'success',
                'message' => '获取成功',
                'accounts' => $account_list
            );
        }
    }
}
echo json_encode($data, JSON_UNESCAPED_UNICODE);
exit;
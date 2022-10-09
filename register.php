<?php
include("header.php");
//如果已登录则跳转到用户界面
if (isset($_SESSION["isLogin"]) and $_SESSION["isLogin"]) {
    echo "<script>alert('你已登录!');window.location.href='userindex.php';</script>";
    exit;
}
if (isset($_POST['register']) && $Sys_config['enable_register']) {
    if ($_POST["username"] == null or $_POST["password"] == null) {
        echo "<script>alert('用户名或密码不能为空!');window.location.href='index.php#login';</script>";
        exit;
    } else {
        $feed = register($_POST["username"], $_POST["password"]);
        if (!$feed[0]) {
            sleep(6);
            echo "<script>alert('$feed[1]');window.location.href='index.php#login';</script>";
        } else {
            echo "<script>alert('注册成功!');window.location.href='index.php#login';</script>";
        }

    }
}
?>
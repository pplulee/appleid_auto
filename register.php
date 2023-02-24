<?php
include("header.php");
//如果已登录则跳转到用户界面
if (isset($_SESSION["isLogin"]) && $_SESSION["isLogin"]) {
    alert("error","你已登录！",1000,"userindex.php");
    exit;
}
if (isset($_POST['register']) && $Sys_config['enable_register']) {
    if ($_POST["username"] == null || $_POST["password"] == null) {
        alert("error","用户名或密码不能为空！",2000,"index.php#register");
        exit;
    } else {
        $feed = register($_POST["username"], $_POST["password"]);
        if (!$feed[0]) {
            alert("error",$feed[1],2000,"index.php#register");
        } else {
            alert("success","注册成功",2000,"index.php#login");
        }

    }
}
?>
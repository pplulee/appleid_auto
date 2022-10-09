<?php
include("header.php");
//如果已登录则跳转到用户界面
if (isset($_SESSION["isLogin"]) and $_SESSION["isLogin"]) {
    echo "<script>alert('你已登录!');window.location.href='userindex.php';</script>";
    exit;
}

if (isset($_POST['login'])) {
    if ($_POST["username"] == null or $_POST["password"] == null) {
        echo "<script>alert('邮箱或密码不能为空!');window.location.href='index.php#login';</script>";
        exit;
    } else {
        $result = login($_POST["username"], $_POST["password"]);
        if ($result[0]) {
            $_SESSION['isLogin'] = true;
            $_SESSION['user_id'] = get_id_by_username($_POST["username"]);
            echo "<script>window.location.href='userindex.php';</script>";
        } else {
            echo "<script>alert('$result[1]');window.location.href='index.php#login';</script>";
        }
    }
}
?>
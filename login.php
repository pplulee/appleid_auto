<?php
include("header.php");
//如果已登录则跳转到用户界面
if (isset($_SESSION["isLogin"]) and $_SESSION["isLogin"]) {
    alert("error","你已登录！",1000,"userindex.php");
    exit;
}

if (isset($_POST['login'])) {
    if ($_POST["username"] == "" or $_POST["password"] == "") {
        alert("error","用户名或密码不能为空！",2000,"index.php#login");
        exit;
    } else {
        $result = login($_POST["username"], $_POST["password"]);
        if ($result[0]) {
            $_SESSION['isLogin'] = true;
            $_SESSION['user_id'] = get_id_by_username($_POST["username"]);
            echo "<script>window.location.href='userindex.php';</script>";
        } else {
            alert("error",$result[1],2000,"index.php#login");
        }
    }
}
?>
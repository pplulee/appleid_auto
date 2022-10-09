<?php
include("include/common.php");
if (!isset($_SESSION['isLogin'])) {
    $_SESSION['isLogin'] = false;
}
if ((!$_SESSION['isLogin']) and (!in_array(php_self(), array("index.php", "login.php", "register.php")))) {
    echo "<script>window.location.href='index.php#login';</script>"; // Redirect to login page
    exit;
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="userindex.php">AppleID 自动化管理</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php
                if ($_SESSION['isLogin']) {
                    echo "<li class='nav-item'>
                    <a class='nav-link' href='userindex.php'>用户中心</a>
                </li>
                <li class='nav-item'>
                    <a class='nav-link' href='account.php'>账号管理</a>
                </li>
                <li class='nav-item'>
                    <a class='nav-link' href='task.php'>任务管理</a>
                </li>
                <li class='nav-item'>
                    <a class='nav-link' href='user_info.php'>个人信息</a>
                </li>";
                } else {
                    echo "<li class='nav-item'>
                    <a class='nav-link' href='index.php'>网站首页</a>
                </li>";
                } ?>

                <?php if ((isset($_SESSION['user_id'])) and (isadmin($_SESSION['user_id']))) {
                    echo "
                <li class='nav-item'>
                    <a class='nav-link' href='/admin'>管理面板</a>
                </li>";
                } ?>
            </ul>
            <?php if ($_SESSION['isLogin']) {
                echo '<a href="userindex.php?logout" class="btn btn-danger">登出</a>';
            } else {
                echo '<a href="index.php#login" class="btn btn-success">登录</a>';
            } ?>
        </div>
    </div>
</nav>
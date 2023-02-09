<?php
include("header.php");
if (isset($_GET['logout'])) {
    logout();
    echo "<script>window.location.href='../index.php';</script>";
    exit();
}
?>
<head>
    <title>管理员面板</title>
</head>
<body>
<div class="container" style="margin-top: 1%">
    <div class="card border-dark">
        <h3 class="card-header">服务器信息</h3>
        <ul class="list-group">
            <li class="list-group-item">
                <b>总账号数:</b> <?php echo $conn->query("SELECT id FROM account;")->rowCount(); ?>
            </li>
            <li class="list-group-item">
                <b>总用户数:</b> <?php echo $conn->query("SELECT id FROM user;")->rowCount(); ?>
            </li>
            <li class="list-group-item">
                <b>PHP版本:</b><?php echo phpversion() ?>
                <?php if (ini_get('safe_mode')) {
                    echo '线程安全';
                } else {
                    echo '非线程安全';
                } ?>
            </li>
            <li class="list-group-item">
                <b>MySQL版本:</b> <?php echo mysqli_get_server_version($conn) ?>
            </li>
            <li class="list-group-item">
                <b>网页服务器:</b> <?php echo $_SERVER['SERVER_SOFTWARE'] ?>
            </li>
            <li class="list-group-item">
                <b>服务器系统:</b><?php echo php_uname('a') ?>
            </li>
            <li class="list-group-item">
                <b>最大运行时间:</b> <?php echo ini_get('max_execution_time') ?>s
            </li>
            <li class="list-group-item">
                <b>POST大小限制:</b> <?php echo ini_get('post_max_size'); ?>
            </li>
            <li class="list-group-item">
                <b>文件上传大小限制:</b> <?php echo ini_get('upload_max_filesize'); ?>
            </li>
        </ul>
    </div>
</div>
</body>
<?php
include "include/common.php";
if (!isset($_GET['link'])){
    echo "分享链接不存在";
    exit;
}
$account = new account(get_share_account_id($_GET['link']));
if ($account->id==-1){
    echo "分享链接不存在";
    exit;
}
?>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>信息</title>
</head>
<body>
<div class="container" style="align-self: center; position: absolute;width: <?php echo ((isMobile())?"auto":"20%"); ?>; margin-top:1rem">
    <div class="card" style="width: 20rem;">
        <div class="card-body">
            <h5 class="card-title">账号信息</h5>
            <h6 class="card-text"><?php echo $account->username ?></h6>
            <p class="card-subtitle mb-2 text-muted">上次检测时间：<?php echo $account->last_check ?></p>
            <!-- 如果当前时间与检测时间误差不大于10分钟，则显示状态正常 -->
            <p class="card-subtitle mb-2 text-muted">状态：<?php echo ((time()-strtotime($account->last_check))<600)?"<font color='#549A31'>正常</font>":"<font color='#B40404'>异常</font>" ?></p>
            <button id="username" class="btn btn-primary" data-clipboard-text="<?php echo $account->username ?>">复制账号</button>
            <button id="password" class="btn btn-success" data-clipboard-text="<?php echo $account->password ?>">复制密码</button>
            <script>
                var username_btn = document.getElementById('username');
                var clipboard_username = new ClipboardJS(username_btn);
                var password_btn = document.getElementById('password');
                var clipboard_password = new ClipboardJS(password_btn);

                clipboard_username.on('success', function (e) {
                    Swal.fire({icon: 'success',title: '提示',text: '复制成功',timer:1000,timerProgressBar: true});
                });

                clipboard_password.on('success', function (e) {
                    Swal.fire({icon: 'success',title: '提示',text: '复制成功',timer:1000,timerProgressBar: true});
                });
            </script>
        </div>
    </div>
</div>
</body>
</html>
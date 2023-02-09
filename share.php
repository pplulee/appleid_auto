<?php
include "include/common.php";
if (!isset($_GET['link'])) {
    echo "分享链接不存在";
    exit;
}
$account = new account(get_share_account_id($_GET['link']));
if ($account->id == -1) {
    echo "分享链接不存在";
    exit;
}
?>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>账号分享</title>
</head>
<body>
<script>
    var clipboard = new ClipboardJS('.btn');

    function alert_success() {
        Swal.fire({
            icon: 'success',
            title: '提示',
            text: '复制成功',
            timer: 1000,
            timerProgressBar: true
        });
    }
</script>
<div class="container"
     style="align-self: center; position: absolute;width: <?php echo((isMobile()) ? "auto" : "20%"); ?>; margin-top:1rem">
    <div class="card" style="width: 20rem;">
        <div class="card-body">
            <h5 class="card-title">账号信息</h5>
            <h6 class="card-text"><?php echo $account->username ?></h6>
            <?php
            if ($account->frontend_remark != "") {
                echo "<p class='card-subtitle mb-2 text-muted'>备注：$account->frontend_remark</p>";
            }
            ?>
            <p class="card-subtitle mb-2 text-muted">上次检测时间：<?php echo $account->last_check ?></p>
            <p class="card-subtitle mb-2 text-muted">
                状态：<?php echo ($account->message == "正常" && ((time() - strtotime($account->last_check)) < ($account->check_interval + 2) * 60)) ? "<font color='#549A31'>正常</font>" : "<font color='#B40404'>异常</font>" ?></p>

            <button id="username" class="btn btn-primary" data-clipboard-text="<?php echo $account->username ?>"
                    onclick='alert_success()'>复制账号
            </button>
            <button id="password" class="btn btn-success" data-clipboard-text="<?php echo $account->password ?>"
                    onclick='alert_success()'>复制密码
            </button>
        </div>
    </div>
</div>
</body>
</html>
<?php
include "include/common.php";
global $conn;
if (!isset($_GET['link'])) {
    echo "分享链接不存在";
    exit;
}
$result = $conn->query("SELECT * FROM share WHERE share_link = '{$_GET['link']}';");
if ($result->num_rows == 0) {
    echo "分享链接不存在";
    exit;
} else {
    $account_list = $result->fetch_assoc()['account_list'];
    $account_list = explode(",", $account_list);
    if (sizeof($account_list) == 0) {
        echo "无法找到账号";
        exit;
    }
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

    <?php
    foreach ($account_list as $account_id) {
        $account = new account($account_id);
        if ($account->id == -1) {
            continue;
        }
        echo "<div class='card' style='width: 20rem;'>
                <div class='card-body'>
                    <h5 class='card-title'>账号信息</h5>
                    <h6 class='card-text'>$account->username</h6>
                    <p class='card-subtitle mb-2 text-muted'>上次检测时间：$account->last_check</p>
                    <p class='card-subtitle mb-2 text-muted'>状态：" . (((time() - strtotime($account->last_check)) < 600) ? "<font color='#549A31'>正常</font>" : "<font color='#B40404'>异常</font>") . "</p>
                    <button id='username_$account->id' class='btn btn-primary' data-clipboard-text='$account->username' onclick='alert_success()'>复制账号</button>
                    <button id='password_$account->id' class='btn btn-success' data-clipboard-text='$account->password' onclick='alert_success()'>复制密码</button>
                </div>
              </div>
              <br>";
    }
    ?>
</div>
</div>
</body>
</html>
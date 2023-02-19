<?php
include "include/common.php";
global $conn;
if (!isset($_GET['link'])) {
    echo "分享链接不存在";
    exit;
}
$sharepage = new sharepage(get_share_id($_GET['link']));
if ($sharepage->id == -1) {
    echo "分享页面不存在";
    exit;
}
if (sizeof($sharepage->account_list) == 0) {
    echo "无法找到账号";
    exit;
}
if ($sharepage->password != "") {
    if (!isset($_POST['password'])) {
        echo "<form action='share_accounts.php?link=" . $_GET['link'] . "' method='post'>
                <input type='password' name='password' placeholder='请输入密码'>
                <input type='submit' value='提交'>
              </form>";
        exit;
    } else {
        if ($sharepage->password != $_POST['password']) {
            echo "密码错误";
            exit;
        }
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
foreach ($sharepage->account_list as $account_id) {
    $account = new account($account_id);
    if ($account->id == -1) {
        continue;
    }
    $remark = "";
    if ($account->frontend_remark != "") {
        $remark = "<p class='card-subtitle mb-2 text-muted'>备注：$account->frontend_remark</p>";
    }
    echo "<div class='card border border-3 border-info shadow-lg' style='width: 20rem;'>
            <div class='card-body'>
                <h5 class='card-title'>账号信息</h5>
                <h6 class='card-text'>$account->username</h6>
                " . $remark . "
                <p class='card-subtitle mb-2 text-muted'>上次检测时间：$account->last_check</p>
                <p class='card-subtitle mb-2 text-muted'>状态：" . (($account->message == "正常" && ((time() - strtotime($account->last_check)) < (($account->check_interval + 2) * 60))) ? "<img src='resources/img/icons8-checkmark.svg' width='30' height='30'><span style='color: #549A31'>正常</span>" : "<img src='resources/img/icons8-cancel.svg' width='30' height='30'><span style='color: #B40404'>异常</span>") . "</p>
                <button id='username_$account->id' class='btn btn-primary' data-clipboard-text='$account->username' onclick='alert_success()'>复制账号</button>
                <button id='password_$account->id' class='btn btn-success' data-clipboard-text='$account->password' onclick='alert_success()'>复制密码</button>
            </div>
          </div>
          <br>";
}
echo $sharepage->html;
?>
</div>
</body>
</html>
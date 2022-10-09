<!DOCTYPE html>
<html lang="zh-CN">
<?php include "head.php"; ?>
<head>
    <meta charset="UTF-8">
    <title>信息</title>
</head>
<body>
<div class="container" style="align-self: center; position: relative;width: <?php echo ((isMobile())?"auto":"20%"); ?>;margin-top: 15%">
    <div class="card" style="width: 18rem;">
        <div class="card-body">
            <h5 class="card-title">账号信息</h5>
            <h6 class="card-subtitle mb-2 text-muted">xxx@example.com</h6>
            <p class="card-text">最后更新时间： xxx</p>
            <button id="username" class="btn btn-primary" data-clipboard-text="username@example.com">复制账号</button>
            <button id="password" class="btn btn-success" data-clipboard-text="password">复制密码</button>
            <script src="include/clipboard.min.js"></script>
            <script>
                var username_btn = document.getElementById('username');
                var clipboard_username = new ClipboardJS(username_btn);
                var password_btn = document.getElementById('password');
                var clipboard_password = new ClipboardJS(password_btn);

                clipboard_username.on('success', function (e) {
                    alert("复制成功");
                });

                clipboard_password.on('success', function (e) {
                    alert("复制成功");
                });

                clipboard_password.on('error', function (e) {
                    console.info('Action:', e.action);
                    console.info('Text:', e.text);
                    console.info('Trigger:', e.trigger);
                });

                clipboard_username.on('error', function (e) {
                    console.info('Action:', e.action);
                    console.info('Text:', e.text);
                    console.info('Trigger:', e.trigger);
                });
            </script>
        </div>
    </div>
</div>
</body>
</html>
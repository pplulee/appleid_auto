<?php
include("header.php");
$currentuser = new user($_SESSION['user_id']);
?>
<title>账号管理</title>
<div class="container" style="padding-top:70px;">
    <div class="col-md-12 center-block" style="float: none;">
        <div class="table-responsive">
            <a href='account_edit.php?action=add' class='btn btn-secondary'>添加账号</a>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>账号</th>
                    <th>密码</th>
                    <th>备注</th>
                    <th>上次检查</th>
                    <th>检查间隔</th>
                    <th>操作</th>
                </tr>
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
                </thead>
                <?php
                global $conn;
                $result = $conn->query("SELECT id,username,password,remark,last_check,share_link,check_interval FROM account WHERE owner = '$currentuser->user_id';");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $share_link = "{$Sys_config['apiurl']}/share.php?link={$row['share_link']}";
                        echo "<tr><td>{$row['username']}</td><td>{$row['password']}</td><td>{$row['remark']}</td><td>{$row['last_check']}</td><td>{$row['check_interval']}</td><td> <button id='share_link' class='btn btn-success ' data-clipboard-text='$share_link' onclick='alert_success()'>复制链接</button> <a href='account_edit.php?action=edit&id={$row['id']}' class='btn btn-secondary'>编辑</a> <a href='account_edit.php?action=delete&id={$row['id']}' class='btn btn-danger'>删除</a></td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>暂无账号</td></tr>";
                }
                ?>
            </table>

        </div>
    </div>
</div>

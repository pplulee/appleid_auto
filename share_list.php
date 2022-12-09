<?php
include("header.php");
?>
<title>分享页管理</title>
<div class="container" style="padding-top:70px;">
    <div class="col-md-12 center-block" style="float: none;">
        <div class="table-responsive">
            <a href='share_edit.php?action=add' class='btn btn-secondary'>添加分享页</a>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>页面ID</th>
                    <th>账号数量</th>
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
                $result = $conn->query("SELECT share_id, share_link, account_list FROM share WHERE owner = '{$_SESSION['user_id']}';");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $account_list = explode(',', $row['account_list']);
                        $account_count = count($account_list);
                        $share_link = "{$Sys_config['apiurl']}/share_accounts.php?link={$row['share_link']}";
                        echo "<tr><td>{$row['share_id']}</td><td>$account_count</td><td> <button id='share_link' class='btn btn-success ' data-clipboard-text='$share_link' onclick='alert_success()'>复制链接</button> <a href='share_edit.php?action=edit&id={$row['share_id']}' class='btn btn-secondary'>编辑</a> <a href='share_edit.php?action=delete&id={$row['share_id']}' class='btn btn-danger'>删除</a></td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>没有分享页</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
</div>

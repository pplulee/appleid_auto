<?php
include("header.php");
?>
<title>任务管理</title>
<div class="container" style="padding-top:70px;">
    <div class="col-md-12 center-block" style="float: none;">
        <div class="table-responsive">
            <a href='task_edit.php?action=add' class='btn btn-secondary'>添加任务</a>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>账号</th>
                    <th>检查间隔</th>
                    <th>上次更新</th>
                    <th>启用通知</th>
                    <th>操作</th>
                </tr>
                </thead>
                <?php
                global $conn;
                $result = $conn->query("SELECT id,account_id,check_interval,last_update,tgbot_chatid,tgbot_token FROM task WHERE owner = '{$_SESSION['user_id']}';");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $account_name = get_account_username($row['account_id']);
                        $enable_tgbot = ($row['tgbot_chatid'] != "" && $row['tgbot_token'] != "") ? "是" : "否";
                        echo "<tr><td>$account_name</td><td>{$row['check_interval']}</td><td>{$row['last_update']}</td><td>$enable_tgbot</td><td><a href='task_edit.php?action=edit&id={$row['id']}' class='btn btn-secondary'>编辑</a> <a href='task_edit.php?action=delete&id={$row['id']}' class='btn btn-danger'>删除</a></td></tr>";
                    }
                }
                ?>
            </table>
        </div>
    </div>
</div>

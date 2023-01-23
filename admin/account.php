<?php
include("header.php");
$currentuser = new user($_SESSION['user_id']);
?>
<title>账号管理</title>
<div class="container" style="padding-top:70px;">
    <div class="col-md-12 center-block" style="float: none;">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>账号</th>
                    <th>备注</th>
                    <th>所属用户</th>
                    <th>上次检查</th>
                    <th>检查间隔</th>
                    <th>操作</th>
                </tr>
                </thead>
                <?php
                global $conn;
                $result = $conn->query("SELECT id,username,remark,owner,last_check,check_interval FROM account;");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $user_name = get_username_by_id($row['owner']);
                        echo "<tr><td>{$row['id']}</td><td>{$row['username']}</td><td>{$row['remark']}</td><td>$user_name</td><td>{$row['last_check']}</td><td>{$row['check_interval']}</td><td><a href='account_edit.php?action=edit&id={$row['id']}' class='btn btn-secondary'>编辑</a> <a href='account_edit.php?action=delete&id={$row['id']}' class='btn btn-danger'>删除</a></td></tr>";
                    }
                }else{
                    echo "<tr><td colspan='7'>暂无账号</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
</div>

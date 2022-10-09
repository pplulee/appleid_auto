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
                    <th>操作</th>
                </tr>
                </thead>
                <?php
                global $conn;
                $result = $conn->query("SELECT id,username,password,remark FROM account WHERE owner = '$currentuser->user_id';");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr><td>{$row['username']}</td><td>{$row['password']}</td><td>{$row['remark']}</td><td><a href='account_edit.php?action=edit&id={$row['id']}' class='btn btn-secondary'>编辑</a> <a href='account_edit.php?action=delete&id={$row['id']}' class='btn btn-danger'>删除</a></td></tr>";
                    }
                }
                ?>
            </table>
        </div>
    </div>
</div>

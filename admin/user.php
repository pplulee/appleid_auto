<?php
include("header.php");
?>
<title>用户管理</title>
<div class="container" style="padding-top:70px;">
    <div class="col-md-12 center-block" style="float: none;">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>用户ID</th>
                    <th>用户名</th>
                    <th>操作</th>
                </tr>
                </thead>
                <?php
                global $conn;
                $result = $conn->prepare("SELECT id,username FROM user;");
                $result->execute();
                if ($result->rowCount() > 0) {
                    while ($row = $result->fetch()) {
                        echo "<tr><th>{$row['id']}</th><td>{$row['username']}</td><td><a href='user_edit.php?action=edit&id={$row['id']}' class='btn btn-secondary'>编辑</a> <a href='user_edit.php?action=delete&id={$row['id']}' class='btn btn-danger'>删除</a></td></tr>";
                    }
                }
                ?>
            </table>
        </div>
    </div>
</div>

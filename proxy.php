<?php
include("header.php");
?>
<title>代理池管理</title>
<div class="container" style="padding-top:70px;">
    <div class="col-md-12 center-block" style="float: none;">
        <div class="table-responsive">
            <a href='proxy_edit.php?action=add' class='btn btn-secondary'>添加代理</a>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>代理ID</th>
                    <th>协议</th>
                    <th>地址</th>
                    <th>状态</th>
                    <th>上次使用</th>
                    <th>操作</th>
                </tr>
                </thead>
                <?php
                global $conn;
                $result = $conn->prepare("SELECT * FROM proxy WHERE owner = :owner;");
                $result->execute(['owner' => $_SESSION['user_id']]);
                if ($result->rowCount() > 0) {
                    while ($row = $result->fetch()) {
                        $status = $row['status'] ? "启用" : "禁用";
                        echo "<tr><td>{$row['id']}</td><td>{$row['protocol']}</td><td>{$row['content']}</td><td>$status</td><td>{$row['last_use']}</td><td> <a href='proxy_edit.php?action=edit&id={$row['id']}' class='btn btn-secondary'>编辑</a> <a href='proxy_edit.php?action=delete&id={$row['id']}' class='btn btn-danger'>删除</a></td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>未添加代理</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
</div>

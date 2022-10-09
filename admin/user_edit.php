<?php
include("header.php");

if (isset($_POST['submit'])) {
    $currentuser = new user($_POST['userid']);
    if (($currentuser->user_id) == -1) {
        alert("用户不存在");
        exit;
    }
    $currentuser->update($_POST['email'], $_POST['isadmin']);
    if ($_POST['password'] != "") {
        $currentuser->change_password($_POST['password']);
    }
    echo '<div class="alert alert-success" role="alert"><p>保存成功</p></div>';
    echo '<script>window.setTimeout("window.location=\'user.php\'",800);</script>';
    exit;
}

if (isset($_GET['action'])) {
    if (!isset($_GET["id"])) {
        echo '<div class="alert alert-danger" role="alert"><p>参数错误</p></div>';
        exit;
    }
    $currentuser = new user($_GET["id"]);
    if ($currentuser->user_id == 0) {
        echo '<div class="alert alert-danger" role="alert"><p>用户不存在</p></div>';
        exit;
    }
    switch ($_GET["action"]) {
        case "edit":
        {
            break;
        }
        case "delete":
        {
            $currentuser->delete_account();
            echo '<div class="alert alert-success" role="alert"><p>用户删除成功</p></div>';
            echo '<script>window.setTimeout("window.location=\'user.php\'",800);</script>';
            exit;
        }
        default:
        {
            echo '<div class="alert alert-danger" role="alert"><p>action参数错误</p></div>';
            exit;
        }
    }
}

?>
<div class="container" style="margin-top: 2%;width: <?php echo (isMobile()) ? "auto" : "30%"; ?>;">
    <div class='card border-dark'>
        <h4 class='card-header bg-primary text-white text-center'>编辑用户</h4>
        <form action='' method='post' style="margin: 20px;">
            <div class="input-group mb-3">
                <span class='input-group-text' id='userid'>用户ID</span>
                <input type='text' class='form-control' name='userid'
                       autocomplete='off' <?php echo "value='$currentuser->user_id'"; ?>
                       readonly>
            </div>
            <div class="input-group mb-3">
                <span class='input-group-text' id='username'>用户名</span>
                <input type='email' class='form-control' name='user'
                       autocomplete='off' <?php echo "value='$currentuser->username'"; ?>
                       required>
            </div>
            <div class="input-group mb-3">
                <span class='input-group-text' id='password'>密码</span>
                <input type='password' class='form-control' name='password' autocomplete='off'
                       placeholder='不修改请留空'>
            </div>
            <div class="input-group mb-3">
                <span class='input-group-text' id='isadmin'>管理员</span>
                <select class="btn btn-info dropdown-toggle" name='isadmin' required>
                    <option value=0>否</option>
                    <option value=1 <?php if ($currentuser->is_admin) echo "selected" ?>>是</option>
                </select>
            </div>
            <input type='submit' class='btn btn-primary' name='submit' value='保存'>
        </form>
    </div>
</div>
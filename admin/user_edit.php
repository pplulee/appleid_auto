<?php
include("header.php");

if (isset($_POST['submit'])) {
    $currentuser = new user($_POST['userid']);
    if (($currentuser->user_id) == -1) {
        alert("error","用户不存在！",2000,"user.php");
        exit;
    }
    $currentuser->update($_POST['username'], $_POST['isadmin']);
    if ($_POST['password'] != "") {
        $currentuser->change_password($_POST['password']);
    }
    alert("success","修改成功！",2000,"user.php");
    exit;
}

if (isset($_GET['action'])) {
    if (!isset($_GET["id"])) {
        alert("error","参数错误！",2000,"user.php");
        exit;
    }
    $currentuser = new user($_GET["id"]);
    if ($currentuser->user_id == 0) {
        alert("error","用户不存在！",2000,"user.php");
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
            alert("success","删除成功！",2000,"user.php");
            exit;
        }
        default:
        {
            alert("error","参数错误！",2000,"user.php");
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
                <input type='text' class='form-control' name='username'
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
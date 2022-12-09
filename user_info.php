<?php
include("header.php");
$currentuser = new user($_SESSION['user_id']);
if (isset($_POST['submit'])) {
    if ($_POST['password'] != "") {
        $currentuser->change_password($_POST['password']);
        alert("success", "修改成功", 2000,"userindex.php");
    }
}
?>
<div class="container" style="margin-top: 2%;width: <?php echo (isMobile()) ? "auto" : "50%"; ?>;">
    <div class='card border-dark'>
        <h4 class='card-header bg-primary text-white text-center'>个人信息</h4>
        <form action='' method='post' style="margin: 20px;">
            <div class="input-group mb-3">
                <span class='input-group-text' id='username'>用户名</span>
                <input type='email' disabled class='form-control' name='username'
                       value='<?php echo $currentuser->username; ?>'>
            </div>
            <div class="input-group mb-3">
                <span class='input-group-text' id='name'>密码</span>
                <input type='password' class='form-control' name='password' placeholder='不修改请留空'>
            </div>
            <input type='submit' class='btn btn-primary' name='submit' value='保存'>
        </form>
    </div>
</div>

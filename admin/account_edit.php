<?php
include("header.php");
global $conn;
if (isset($_POST['submit'])) {
    switch ($_GET['action']) {
        case "edit":
            $account = new account($_GET['id']);
            $enable_check_password_correct = isset($_POST['enable_check_password_correct']) ? 1 : 0;
            $enable_delete_devices = isset($_POST['enable_delete_devices']) ? 1 : 0;
            $account->update($_POST['username'], $_POST['password'], $_POST['remark'], $_POST['dob'], $_POST['question1'], $_POST['answer1'], $_POST['question2'], $_POST['answer2'], $_POST['question3'], $_POST['answer3'], $_POST['owner'], $_POST['share_link'], $_POST['check_interval'], $_POST['frontend_remark'], $enable_check_password_correct, $enable_delete_devices);
            alert("success", "修改成功！", 2000, "account.php");
            exit;
        default:
            alert("error", "未知错误", 2000, "account.php");
            exit;
    }
}
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case "delete":
        {
            $account = new account($_GET['id']);
            $account->delete();
            alert("success", "删除成功！", 2000, "account.php");
            exit;
        }
        case "edit":
        {
            $account = new account($_GET['id']);
            $width = isMobile() ? "auto" : "60%";
            $question1 = array_keys($account->question)[0];
            $question2 = array_keys($account->question)[1];
            $question3 = array_keys($account->question)[2];
            $answer1 = $account->question[$question1];
            $answer2 = $account->question[$question2];
            $answer3 = $account->question[$question3];
            $check_interval = $account->check_interval;
            $check_password_checked = $account->enable_check_password_correct ? "checked" : "";
            $delete_devices_checked = $account->enable_delete_devices ? "checked" : "";
            echo "<div class='container' style='margin-top: 2%; width: $width;'>
                    <div class='card border-dark'>
                        <h4 class='card-header bg-primary text-white text-center'>编辑账号</h4>
                        <form action='' method='post' style='margin: 20px;'>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='id'>ID</span>
                                <input type='text' class='form-control' name='id' required disabled autocomplete='off' value='$account->id'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='owner'>拥有者ID</span>
                                <input type='text' class='form-control' name='owner' required autocomplete='off' value='$account->owner'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='username'>用户名</span>
                                <input type='text' class='form-control' name='username' required autocomplete='off' value='$account->username'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='password'>密码</span>
                                <input type='text' class='form-control' name='password' required autocomplete='off' value='$account->password'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='remark'>备注</span>
                                <input type='text' class='form-control' name='remark' autocomplete='off' value='$account->remark'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='frontend_remark'>前端备注</span>
                                <input type='text' class='form-control' name='frontend_remark' placeholder='账号的说明，在分享页显示' autocomplete='off' value='$account->frontend_remark'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='dob'>生日</span>
                                <input type='text' class='form-control' name='dob' placeholder='mmddyyyy' required autocomplete='off' value='$account->dob'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='question1'>问题1</span>
                                <input type='text' class='form-control' name='question1' required autocomplete='off' value='$question1'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='answer1'>答案1</span>
                                <input type='text' class='form-control' name='answer1' required autocomplete='off' value='$answer1'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='question2'>问题2</span>
                                <input type='text' class='form-control' name='question2' required autocomplete='off' value='$question2'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='answer2'>答案2</span>
                                <input type='text' class='form-control' name='answer2' required autocomplete='off' value='$answer2'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='question3'>问题3</span>
                                <input type='text' class='form-control' name='question3' required autocomplete='off' value='$question3'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='answer3'>答案3</span>
                                <input type='text' class='form-control' name='answer3' required autocomplete='off' value='$answer3'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='share_link'>分享代码</span>
                                <input type='text' class='form-control' name='share_link' value='$account->share_link' required autocomplete='off'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='last_check'>上次检查</span>
                                <input type='text' class='form-control' name='share_link' value='$account->last_check' required disabled>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='message'>状态</span>
                                <input type='text' class='form-control' name='message' value='$account->message' required autocomplete='off' disabled>
                            </div>
                            <div class='input-group mb-3'>
                                <div class='form-check form-switch'>
                                  开启密码正确检测<input class='form-check-input' type='checkbox' name='enable_check_password_correct' $check_password_checked>
                                </div>
                            </div>
                            <div class='input-group mb-3'>
                                <div class='form-check form-switch'>
                                  开启删除设备<input class='form-check-input' type='checkbox' name='enable_delete_devices' $delete_devices_checked>
                                </div>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='check_interval'>检查间隔</span>
                                <input type='number' class='form-control' name='check_interval' required autocomplete='off' value='$check_interval'>
                            </div>
                            <input type='submit' name='submit' class='btn btn-primary btn-block' value='保存'>
                        </form>
                    </div>
                </div>";
            exit;
        }
        default:
        {
            alert("danger", "未知操作！", 2000, "account.php");
            exit;
        }
    }
}
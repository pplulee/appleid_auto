<?php
include("header.php");
global $conn;
if (isset($_POST['submit'])) {
    switch ($_GET['action']) {
        case "add":
        {
            if (get_account_id($_POST['username']) != -1) {
                alert("warning", "账号已存在", 2000, "account.php");
                exit;
            }
            $conn->query("INSERT INTO account (username, password, remark, dob, question1, answer1,question2,answer2,question3,answer3,owner,share_link,check_interval) VALUES ('{$_POST['username']}','{$_POST['password']}','{$_POST['remark']}','{$_POST['dob']}','{$_POST['question1']}','{$_POST['answer1']}','{$_POST['question2']}','{$_POST['answer2']}','{$_POST['question3']}','{$_POST['answer3']}','{$_SESSION['user_id']}','{$_POST['share_link']}','{$_POST['check_interval']}');");
            alert("success", "添加成功", 2000, "account.php");
            exit;
        }
        case "edit":
        {
            $account = new account($_GET['id']);
            if ($account->owner == $_SESSION['user_id'] || $account->id) {
                $account->update($_POST['username'], $_POST['password'], $_POST['remark'], $_POST['dob'], $_POST['question1'], $_POST['answer1'], $_POST['question2'], $_POST['answer2'], $_POST['question3'], $_POST['answer3'], $_SESSION['user_id'], $_POST['share_link'], $_POST['check_interval']);
                alert("success", "修改成功", 2000, "account.php");
            } else {
                alert("error", "修改失败", 2000, "account.php");
            }
            exit;
        }
        default:
        {
            alert("error", "未知错误", 2000, "account.php");
            exit;
        }
    }
}
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case "delete":
        {
            if (!isset($_GET['id'])) {
                alert("error", "缺少参数", 2000, "account.php");
                exit;
            }
            $account = new account($_GET['id']);
            if ($account->owner == $_SESSION['user_id'] || $account->id) {
                $account->delete();
                alert("success", "删除成功", 2000, "account.php");
            } else {
                alert("error", "删除失败", 2000, "account.php");
            }
            exit;
        }
        case "add":
        {
            $share_link = random_string(12);
            $width = isMobile() ? "auto" : "60%";
            echo "<div class='container' style='margin-top: 2%; width: $width;'>
                    <div class='card border-dark'>
                        <h4 class='card-header bg-primary text-white text-center'>添加账号</h4>
                        <form action='' method='post' style='margin: 20px;'>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='id'>用户名</span>
                                <input type='text' class='form-control' name='username' required autocomplete='off'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='password'>密码</span>
                                <input type='text' class='form-control' name='password' required autocomplete='off'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='remark'>备注</span>
                                <input type='text' class='form-control' name='remark' autocomplete='off'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='dob'>生日</span>
                                <input type='text' class='form-control' name='dob' placeholder='mmddyyyy' required autocomplete='off'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='question1'>问题1</span>
                                <input type='text' class='form-control' name='question1' required autocomplete='off'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='answer1'>答案1</span>
                                <input type='text' class='form-control' name='answer1' required autocomplete='off'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='question2'>问题2</span>
                                <input type='text' class='form-control' name='question2' required autocomplete='off'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='answer2'>答案2</span>
                                <input type='text' class='form-control' name='answer2' required autocomplete='off'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='question3'>问题3</span>
                                <input type='text' class='form-control' name='question3' required autocomplete='off'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='answer3'>答案3</span>
                                <input type='text' class='form-control' name='answer3' required autocomplete='off'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='question2'>分享代码</span>
                                <input type='text' class='form-control' name='share_link' value='$share_link' required autocomplete='off'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='check_interval'>检查间隔</span>
                                <input type='number' class='form-control' name='check_interval' required autocomplete='off' placeholder='单位：分钟' value='10'>
                            </div>
                            <input type='submit' name='submit' class='btn btn-primary btn-block' value='添加'>
                        </form>
                    </div>
                </div>";
            exit;
        }
        case "edit":
        {
            if (!isset($_GET['id'])) {
                alert("error", "缺少参数", 2000, "account.php");
                exit;
            }
            $account = new account($_GET['id']);
            if ($account->id == -1) {
                alert("error", "账号不存在", 2000, "account.php");
                exit;
            }
            if ($account->owner == $_SESSION['user_id']) {
                $width = isMobile() ? "auto" : "60%";
                $question1 = array_keys($account->question)[0];
                $question2 = array_keys($account->question)[1];
                $question3 = array_keys($account->question)[2];
                $answer1 = $account->question[$question1];
                $answer2 = $account->question[$question2];
                $answer3 = $account->question[$question3];
                $check_interval = $account->check_interval;
                echo "<div class='container' style='margin-top: 2%; width: $width;'>
                    <div class='card border-dark'>
                        <h4 class='card-header bg-primary text-white text-center'>编辑账号</h4>
                        <form action='' method='post' style='margin: 20px;'>
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
                                <span class='input-group-text' id='question2'>分享代码</span>
                                <input type='text' class='form-control' name='share_link' value='$account->share_link' required autocomplete='off'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='check_interval'>检查间隔</span>
                                <input type='number' class='form-control' name='check_interval' required autocomplete='off' value='$check_interval'>
                            </div>
                            <input type='submit' name='submit' class='btn btn-primary btn-block' value='保存'>
                        </form>
                    </div>
                </div>";
            } else {
                alert("error", "修改失败", 2000, "account.php");
            }
            exit;
        }
        default:
        {
            alert("error", "未知错误", 2000, "account.php");
            exit;
        }
    }
} else {
    alert("error", "缺少参数", 2000, "account.php");
}
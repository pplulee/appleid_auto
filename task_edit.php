<?php
include("header.php");
global $conn;
if (isset($_POST['submit'])) {
    switch ($_GET['action']) {
        case "add":
        {
            $conn->query("INSERT INTO task (account_id, check_interval,tgbot_chatid,tgbot_token,owner) VALUES ('{$_POST['account_id']}','{$_POST['check_interval']}','{$_POST['tgbot_chatid']}','{$_POST['tgbot_token']}','{$_SESSION['user_id']}');");
            alert("success", "添加成功", 2000, "task.php");
            exit;
        }
        case "edit":
        {
            $task = new task($_GET['id']);
            if ($task->owner == $_SESSION['user_id'] || $task->id) {
                $task->update($_POST['account_id'], $_POST['check_interval'], $_POST['tgbot_chatid'], $_POST['tgbot_token'], $_SESSION['user_id']);
                alert("success", "修改成功", 2000, "task.php");
            } else {
                alert("error", "修改失败", 2000, "task.php");
            }
            exit;
        }
        default:
        {
            alert("error", "未知错误", 2000, "task.php");
            exit;
        }
    }
}
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case "delete":
        {
            if (!isset($_GET['id'])) {
                alert("error", "缺少参数", 2000, "task.php");
                exit;
            }
            $task = new task($_GET['id']);
            if ($task->owner == $_SESSION['user_id'] || $task->id) {
                $task->delete();
                alert("success", "删除成功", 2000, "task.php");
            } else {
                alert("error", "删除失败", 2000, "task.php");
            }
            exit;
        }
        case "add":
        {
            $width = isMobile() ? "auto" : "60%";
            echo "<div class='container' style='margin-top: 2%; width: $width;'>
                    <div class='card border-dark'>
                        <h4 class='card-header bg-primary text-white text-center'>添加任务</h4>
                        <form action='' method='post' style='margin: 20px;'>
                            <div class='input-group mb-3'>
                            <span class='input-group-text' id='account_id'>选择账户</span>
                                <select class='btn btn-info dropdown-toggle' name='account_id' style='margin-left: 20px' required>";
            $result = $conn->query("SELECT id,username FROM account WHERE owner = '{$_SESSION['user_id']}';");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['id']}'>{$row['username']}</option>";
                }
            }
            echo "</select>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='check_interval'>检查间隔（分钟）</span>
                                <input type='number' class='form-control' min='5' name='check_interval' required autocomplete='off'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='tgbot_chatid'>Telegram Bot ChatID</span>
                                <input type='text' class='form-control' name='tgbot_chatid' autocomplete='off' placeholder='不需要请留空' value=''>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='tgbot_chatid'>Telegram Bot Token</span>
                                <input type='text' class='form-control' name='tgbot_token' autocomplete='off' placeholder='不需要请留空' value=''>
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
                alert("error", "缺少参数", 2000, "task.php");
                exit;
            }
            $task = new task($_GET['id']);
            if ($task->owner == $_SESSION['user_id']) {
                $width = isMobile() ? "auto" : "60%";
                echo "<div class='container' style='margin-top: 2%; width: $width;'>
                    <div class='card border-dark'>
                        <h4 class='card-header bg-primary text-white text-center'>编辑任务</h4>
                        <form action='' method='post' style='margin: 20px;'>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='account_id'>选择账户</span>
                                <select class='btn btn-info dropdown-toggle' name='account_id' style='margin-left: 20px' required>";
                $result = $conn->query("SELECT id,username FROM account WHERE owner = '{$_SESSION['user_id']}';");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $selected = $row['id'] == $task->account_id ? "selected" : "";
                        echo "<option $selected value='{$row['id']}'>{$row['username']}</option>";
                    }
                }
                echo "</select>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='check_interval'>检查间隔（分钟）</span>
                                <input type='number' class='form-control' name='check_interval' min='5' required autocomplete='off' value='$task->check_interval'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='tgbot_chatid'>Telegram Bot ChatID</span>
                                <input type='text' class='form-control' name='tgbot_chatid' autocomplete='off' placeholder='不需要请留空' value='$task->tgbot_chatid'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='tgbot_chatid'>Telegram Bot Token</span>
                                <input type='text' class='form-control' name='tgbot_token' autocomplete='off' placeholder='不需要请留空' value='$task->tgbot_token'>
                            </div>
                            <input type='submit' name='submit' class='btn btn-primary btn-block' value='保存'>
                        </form>
                    </div>
                </div>";
            } else {
                alert("error", "修改失败", 2000, "task.php");
            }
            exit;
        }
        default:
        {
            alert("error", "未知错误", 2000, "task.php");
            exit;
        }
    }
} else {
    alert("error", "缺少参数", 2000, "account.php");
}
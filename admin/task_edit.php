<?php
include("header.php");
global $conn;
if (isset($_POST['submit'])) {
    switch ($_GET['action']) {
        case "edit":
            $task = new task($_GET['id']);
            $task->update($_POST['account_id'], $_POST['check_interval'], $_POST['tgbot_chatid'], $_POST['tgbot_token'], $_POST['owner']);
            echo "<div class='alert alert-success' role='alert'><p>修改成功，即将返回</p></div>";
            echo "<script>setTimeout(\"javascript:location.href='task.php'\", 800);</script>";
            exit;
        default:
            echo "<div class='alert alert-danger' role='alert'><p>未知错误</p></div>";
            echo "<script>setTimeout(\"javascript:location.href='task.php'\", 800);</script>";
            exit;
    }
}
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case "delete":
            $task = new task($_GET['id']);
            $task->delete();
            echo "<div class='alert alert-success' role='alert'><p>删除成功，即将返回</p></div>";
            echo "<script>setTimeout(\"javascript:location.href='task.php'\", 800);</script>";
            exit;
        case "edit":
            $task = new task($_GET['id']);
            $width = isMobile() ? "auto" : "60%";
            echo "<div class='container' style='margin-top: 2%; width: $width;'>
                    <div class='card border-dark'>
                        <h4 class='card-header bg-primary text-white text-center'>编辑任务</h4>
                        <form action='' method='post' style='margin: 20px;'>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='owner'>ID</span>
                                <input type='number' class='form-control' name='id' disabled required autocomplete='off' value='$task->id'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='owner'>拥有者ID</span>
                                <input type='number' class='form-control' name='owner' required autocomplete='off' value='$task->owner'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='check_interval'>检查间隔（分钟）</span>
                                <input type='number' class='form-control' name='check_interval' min='5' required autocomplete='off' value='$task->check_interval'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='account_id'>账号ID</span>
                                <input type='number' class='form-control' name='account_id' required autocomplete='off' value='$task->account_id'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='tgbot_chatid'>Telegram Bot ChatID</span>
                                <input type='text' class='form-control' name='tgbot_chatid' autocomplete='off' placeholder='不需要请留空' value='$task->tgbot_chatid'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='tgbot_chatid'>Telegram Bot Token</span>
                                <input type='text' class='form-control' name='tgbot_token' autocomplete='off' placeholder='不需要请留空' value='$task->tgbot_token'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='last_update'>上次更新</span>
                                <input type='text' class='form-control' name='last_update' autocomplete='off' disabled value='$task->last_update'>
                            </div>
                            <input type='submit' name='submit' class='btn btn-primary btn-block' value='保存'>
                        </form>
                    </div>
                </div>";
            exit;
        default:
            echo "<div class='alert alert-danger' role='alert'><p>未知错误</p></div>";
            echo "<script>setTimeout(\"javascript:location.href='task.php'\", 800);</script>";
            exit;
    }
}
<?php
include("header.php");
global $conn;
if (isset($_POST['submit'])) {
    switch ($_GET['action']) {
        case "edit":
        {
            if (!isset($_GET['id'])) {
                alert("error", "缺少参数", 2000, "shares.php");
                exit;
            }
            if (!isset($_POST['account_list'])) {
                alert("error", "请至少添加一个账户", 2000, "shares.php");
                exit;
            }
            $sharepage = new sharepage($_GET['id']);
            $new_share_link = $_POST['share_link'];
            if ($sharepage->id==-1) {
                alert("error", "分享页面ID不存在", 2000, "shares.php");
                exit;
            }
            if ($sharepage->share_link != $new_share_link && check_sharelink_exist($new_share_link)) {
                alert("error", "分享链接已存在，无法重复添加", 2000, "shares.php");
                exit;
            }
            $sharepage->update($_POST['share_link'], $_POST['password'],$_POST['account_list'],$_POST['owner'],$_POST['html']);
            alert("success", "修改成功", 2000, "shares.php");
            exit;
        }
        default:
        {
            alert("error", "未知错误", 2000, "shares.php");
            exit;
        }
    }
}

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case "delete":
        {
            if (!isset($_GET['id'])) {
                alert("error", "缺少参数", 2000, "shares.php");
                exit;
            }
            $sharepage = new sharepage($_GET['id']);
            if ($sharepage->id == -1) {
                alert("error", "分享页面ID不存在", 2000, "shares.php");
                exit;
            }else{
                $sharepage->delete();
            }
            alert("success", "删除成功", 2000, "shares.php");
            exit;
        }
        case "edit":
        {
            if (!isset($_GET['id'])) {
                alert("error", "缺少参数", 2000, "shares.php");
                exit;
            }
            $sharepage = new sharepage($_GET['id']);
            $width = isMobile() ? "auto" : "60%";
            if ($sharepage->id == -1) {
                alert("error", "分享页面ID不存在", 2000, "shares.php");
                exit;
            }
            $account_list_result = $conn->prepare("SELECT id,username FROM account WHERE owner=:owner;");
            $account_list_result->execute(['owner' => $sharepage->owner]);
            if ($account_list_result->rowCount() == 0) {
                alert("warning", "用户没有账号", 2000, "account.php");
                exit;
            } else {
                $account_list = array();
                while ($row = $account_list_result->fetch()) {
                    $account_list[$row['id']] = $row['username'];
                }
            }
            echo "<div class='container' style='margin-top: 2%; width: $width;'>
                    <div class='card border-dark'>
                        <h4 class='card-header bg-primary text-white text-center'>添加分享页</h4>
                        <form action='' method='post' style='margin: 20px;'>
                            <span class='input-group-text' id='account_list'>请选择账号</span>
                            <div class='form-check mb-3'>";
            foreach ($account_list as $id => $username) {
                $selected = in_array($id, $sharepage->account_list) ? "checked" : "";
                echo "$username <input class='form-check-input' type='checkbox' role='switch' name='account_list[]' $selected value='$id'><br>";
            }
            echo "</div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='owner'>用户ID</span>
                                <input type='text' class='form-control' name='owner' value='$sharepage->owner' required autocomplete='off'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='share_link'>分享代码</span>
                                <input type='text' class='form-control' name='share_link' value='$sharepage->share_link' required autocomplete='off'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='password'>页面密码</span>
                                <input type='text' class='form-control' name='password' value='$sharepage->password' placeholder='留空则不启用密码' autocomplete='off'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='html'>HTML内容</span>
                                <textarea name='html' cols='80' rows=4>$sharepage->html</textarea>
                            </div>
                            <input type='submit' name='submit' class='btn btn-primary btn-block' value='保存'>
                        </form>
                    </div>
                </div>";
            exit;
        }
        default:
        {
            alert("error", "未知错误", 2000, "shares.php");
            exit;
        }
    }
} else {
    alert("error", "缺少参数", 2000, "shares.php");
    exit;
}
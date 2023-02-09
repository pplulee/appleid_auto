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
            $share_link = $_POST['share_link'];
            $share_id = $_GET['id'];
            $share_id_check = $conn->prepare("SELECT * FROM share WHERE share_id = :share_id;");
            $share_id_check->bindParam(':share_id', $share_id);
            $share_id_check->execute();
            if ($share_id_check->rowCount() == 0) {
                alert("error", "分享页面ID不存在", 2000, "shares.php");
                exit;
            } else {
                $share_id_check = $share_id_check->fetch();
            }
            $origin_share_link = $share_id_check['share_link'];
            $share_link_result = $conn->prepare("SELECT owner FROM share WHERE share_link = :share_link;");
            if ($origin_share_link != $share_link && $share_link_result->rowCount() != 0) {
                alert("error", "分享链接已存在，无法重复添加", 2000, "shares.php");
                exit;
            } else {
                $share_link_result = $share_link_result->fetch();
            }
            $accounts = implode(",", $_POST['account_list']);
            $stmt = $conn->prepare("UPDATE share SET account_list = :accounts, share_link=:share_link, owner=:owner WHERE share_id = :share_id;");
            $stmt->execute([
                'accounts' => $accounts,
                'share_link' => $share_link,
                'owner' => $_POST['owner'],
                'share_id' => $share_id]);
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
            $stmt = $conn->prepare("DELETE FROM share WHERE share_id = :share_id;");
            $stmt->execute(['share_id' => $_GET['id']]);
            alert("success", "删除成功", 2000, "shares.php");
            exit;
        }
        case "edit":
        {
            if (!isset($_GET['id'])) {
                alert("error", "缺少参数", 2000, "shares.php");
                exit;
            }
            $width = isMobile() ? "auto" : "60%";
            $page_result = $conn->prepare("SELECT * FROM share WHERE share_id = :share_id;");
            $page_result->execute(['share_id' => $_GET['id']]);
            if ($page_result->rowCount() == 0) {
                alert("error", "页面ID不存在", 2000, "shares.php");
                exit;
            } else {
                $share_result_detail = $page_result->fetch();
            }
            $account_list_result = $conn->prepare("SELECT id,username FROM account WHERE owner=:owner;");
            $account_list_result->execute(['owner' => $_SESSION['user_id']]);
            if ($account_list_result->rowCount() == 0) {
                alert("warning", "用户没有账号", 2000, "account.php");
                exit;
            } else {
                $account_list = array();
                while ($row = $account_list_result->fetch()) {
                    $account_list[$row['id']] = $row['username'];
                }
                $share_account_list = explode(",", $share_result_detail['account_list']);
                $share_link = $share_result_detail['share_link'];
            }
            echo "<div class='container' style='margin-top: 2%; width: $width;'>
                    <div class='card border-dark'>
                        <h4 class='card-header bg-primary text-white text-center'>添加分享页</h4>
                        <form action='' method='post' style='margin: 20px;'>
                            <span class='input-group-text' id='account_list'>请选择账号</span>
                            <div class='form-check mb-3'>";
            foreach ($account_list as $id => $username) {
                $selected = in_array($id, $share_account_list) ? "checked" : "";
                echo "$username <input class='form-check-input' type='checkbox' role='switch' name='account_list[]' $selected value='$id'><br>";
            }
            echo "</div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='owner'>用户ID</span>
                                <input type='text' class='form-control' name='owner' value='{$share_result_detail['owner']}' required autocomplete='off'>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='share_link'>分享代码</span>
                                <input type='text' class='form-control' name='share_link' value='$share_link' required autocomplete='off'>
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
<?php
include("header.php");
global $conn;
if (isset($_POST['submit'])) {
    switch ($_GET['action']) {
        case "add":
        {
            $share_link = $_POST['share_link'];
            $duplicate_result = $conn->query("SELECT share_id FROM share WHERE share_link = '$share_link';");
            if ($duplicate_result->num_rows != 0) {
                alert("error", "分享链接已存在，无法重复添加", 2000, "shares.php");
                exit;
            }
            if (!isset($_POST['account_list'])) {
                alert("error", "请至少添加一个账户", 2000, "shares.php");
                exit;
            }
            $accounts = implode(",", $_POST['account_list']);
            $conn->query("INSERT INTO share (share_link,account_list,owner) VALUES ('$share_link','$accounts','{$_SESSION['user_id']}');");
            alert("success", "添加成功", 2000, "shares.php");
            exit;
        }
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
            // 检查权限
            $share_id_check = $conn->query("SELECT share_link, owner FROM share WHERE share_id = '$share_id';");
            if ($share_id_check->num_rows == 0) {
                alert("error", "分享页面ID不存在", 2000, "shares.php");
                exit;
            } else {
                $share_id_check = $share_id_check->fetch_assoc();
                if ($share_id_check['owner'] != $_SESSION['user_id']) {
                    alert("error", "无权修改", 2000, "shares.php");
                    exit;
                }
            }
            $origin_share_link = $share_id_check['share_link'];
            $share_link_result = $conn->query("SELECT owner FROM share WHERE share_link = '$share_link';");
            if ($origin_share_link != $share_link && $share_link_result->num_rows != 0) {
                alert("error", "分享链接已存在，无法重复添加", 2000, "shares.php");
                exit;
            } else {
                $share_link_result = $share_link_result->fetch_assoc();
                if ($share_link_result['owner'] != $_SESSION['user_id']) {
                    alert("error", "无权修改", 2000, "shares.php");
                    exit;
                }
            }
            $accounts = implode(",", $_POST['account_list']);
            $conn->query("UPDATE share SET account_list = '$accounts', share_link='{$_POST['share_link']}' WHERE share_id = '$share_id';");
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
            $owner_result = $conn->query("SELECT owner FROM share WHERE share_id='{$_GET['id']}';");
            if ($owner_result->num_rows == 0) {
                alert("error", "页面ID不存在", 2000, "shares.php");
                exit;
            } else {
                $owner = $owner_result->fetch_assoc()['owner'];
                if ($owner == $_SESSION['user_id']) {
                    $conn->query("DELETE FROM share WHERE share_id='{$_GET['id']}';");
                    alert("success", "删除成功", 2000, "shares.php");
                    exit;
                } else {
                    alert("warning", "没有权限", 2000, "shares.php");
                    exit;
                }
            }
        }
        case "add":
        {
            $share_link = random_string(12);
            $width = isMobile() ? "auto" : "60%";
            $account_list_result = $conn->query("SELECT id,username FROM account WHERE owner='{$_SESSION['user_id']}';");
            if ($account_list_result->num_rows == 0) {
                alert("warning", "请先添加账号", 2000, "account.php");
                exit;
            } else {
                $account_list = array();
                while ($row = $account_list_result->fetch_assoc()) {
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
                echo "$username <input class='form-check-input' type='checkbox' role='switch' name='account_list[]' value='$id'><br>";
            }
            echo "</div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='share_link'>分享代码</span>
                                <input type='text' class='form-control' name='share_link' value='$share_link' required autocomplete='off'>
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
                alert("error", "缺少参数", 2000, "shares.php");
                exit;
            }
            $width = isMobile() ? "auto" : "60%";
            $page_result = $conn->query("SELECT * FROM share WHERE share_id='{$_GET['id']}' AND owner='{$_SESSION['user_id']}';");
            if ($page_result->num_rows == 0) {
                alert("error", "页面ID不存在", 2000, "shares.php");
                exit;
            }
            $account_list_result = $conn->query("SELECT id,username FROM account WHERE owner='{$_SESSION['user_id']}';");
            if ($account_list_result->num_rows == 0) {
                alert("warning", "请先添加账号", 2000, "account.php");
                exit;
            } else {
                $account_list = array();
                while ($row = $account_list_result->fetch_assoc()) {
                    $account_list[$row['id']] = $row['username'];
                }
                $share_result_detail = $page_result->fetch_assoc();
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
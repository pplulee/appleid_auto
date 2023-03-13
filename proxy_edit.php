<?php
include("header.php");
global $conn;
if (isset($_POST['submit'])) {
    switch ($_GET['action']) {
        case "add":
        {
            $stmt = $conn->prepare("INSERT INTO proxy (protocol, content, status, owner) VALUES (:protocol, :content, :status, :owner)");
            $stmt->execute([
                'protocol' => $_POST['protocol'],
                'content' => $_POST['content'],
                'status' => isset($_POST['status']) ? 1 : 0,
                'owner' => $_SESSION['user_id']
            ]);
            alert("success", "添加成功", 2000, "proxy.php");
            exit;
        }
        case "edit":
        {
            $proxy = new proxy($_GET['id']);
            if ($proxy->owner == $_SESSION['user_id'] || $proxy->id) {
                $proxy->update(
                    $_POST['protocol'],
                    $_POST['content'],
                    $_SESSION['user_id'],
                    isset($_POST['status']));
                alert("success", "修改成功", 2000, "proxy.php");
            } else {
                alert("error", "修改失败", 2000, "proxy.php");
            }
            exit;
        }
        default:
        {
            alert("error", "未知错误", 2000, "proxy.php");
            exit;
        }
    }
}
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case "delete":
        {
            if (!isset($_GET['id'])) {
                alert("error", "缺少参数", 2000, "proxy.php");
                exit;
            }
            $proxy = new proxy($_GET['id']);
            if ($proxy->owner == $_SESSION['user_id'] || $proxy->id) {
                $proxy->delete();
                alert("success", "删除成功", 2000, "proxy.php");
            } else {
                alert("error", "删除失败", 2000, "proxy.php");
            }
            exit;
        }
        case "add":
        {
            $width = isMobile() ? "auto" : "60%";
            echo "<div class='container' style='margin-top: 2%; width: $width;'>
                    <div class='card border-dark'>
                        <h4 class='card-header bg-primary text-white text-center'>添加代理</h4>
                        <form action='' method='post' style='margin: 20px;'>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='protocol'>协议</span>
                                <select class='form-select' name='protocol'>";
                foreach ($Sys_config["proxy_list"] as $protocol) {
                    echo "<option value='$protocol'>$protocol</option>";
                }
                echo "               </select>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='content'>地址</span>
                                <input type='text' class='form-control' name='content'>
                            </div>
                            <div class='input-group mb-3'>
                                <div class='form-check form-switch'>
                                  启用<input class='form-check-input' type='checkbox' name='status'>
                                </div>
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
            $proxy = new proxy($_GET['id']);
            if ($proxy->id == -1) {
                alert("error", "账号不存在", 2000, "account.php");
                exit;
            }
            if ($proxy->owner == $_SESSION['user_id']) {
                $width = isMobile() ? "auto" : "60%";
                $status = $proxy->status == 1 ? "checked" : "";
                $http_checked = $proxy->protocol == "http" ? "checked" : "";
                $socks5_checked = $proxy->protocol == "socks5" ? "checked" : "";
                echo "<div class='container' style='margin-top: 2%; width: $width;'>
                    <div class='card border-dark'>
                        <h4 class='card-header bg-primary text-white text-center'>添加代理</h4>
                        <form action='' method='post' style='margin: 20px;'>
                            <div class='input-group mb-3'>
                                <div class='input-group mb-3'>
                                    <span class='input-group-text' id='protocol'>协议</span>
                                    <select class='form-select' name='protocol'>";
                foreach ($Sys_config["proxy_list"] as $protocol) {
                    $checked = $proxy->protocol == $protocol ? "selected" : "";
                    echo "<option value='$protocol' $checked>$protocol</option>";
                }
                echo "               </select>
                                </div>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='content'>地址</span>
                                <input type='text' class='form-control' name='content' value='$proxy->content'>
                            </div>
                            <div class='input-group mb-3'>
                                <div class='form-check form-switch'>
                                  启用<input class='form-check-input' type='checkbox' name='status' $status>
                                </div>
                            </div>
                            <div class='input-group mb-3'>
                                <span class='input-group-text' id='last_use'>上次使用</span>
                                <input type='text' class='form-control' name='last_use' value='$proxy->last_use' disabled>
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
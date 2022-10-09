<?php
function logout()
{
    $_SESSION['isLogin'] = false;
    unset($_SESSION['user_id']);
    exit("<script>Swal.fire({icon: 'success',title: '成功',text: '已成功注销！',timer:2000,timerProgressBar: true});setTimeout(\"javascript:location.href='index.php'\", 2000);</script>");
}

function random_string($length): string
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $str = '';
    for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}

function isadmin($id): bool
{
    global $conn;
    return $conn->query("SELECT is_admin FROM user WHERE id='$id';")->fetch_assoc()["is_admin"] == 1;
}

function get_account_username($id): string
{
    global $conn;
    return $conn->query("SELECT username FROM account WHERE id='$id';")->fetch_assoc()["username"];
}

function get_id_by_username($username): int
{
    global $conn;
    $result = $conn->query("SELECT id FROM user WHERE username='$username';");
    if ($result->num_rows == 0) {
        return -1;
    } else {
        return $result->fetch_assoc()["id"];
    }
}

function get_accoubt_id($username): int
{
    global $conn;
    $result = $conn->query("SELECT id FROM account WHERE username='$username';");
    if ($result->num_rows == 0) {
        return -1;
    } else {
        return $result->fetch_assoc()["id"];
    }
}

function get_username_by_id($id): string
{
    global $conn;
    $result = $conn->query("SELECT username FROM user WHERE id='$id';");
    if ($result->num_rows == 0) {
        return "";
    } else {
        return $result->fetch_assoc()["username"];
    }
}

function register($username, $password): array
{
    global $conn;
    if (get_id_by_username($username) != -1) {
        return array(false, "用户已存在");
    } else {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $conn->query("INSERT INTO user (username, password) VALUES ('$username', '$password');");
        return array(true, "注册成功");
    }
}

function login($username, $password): array
{
    global $conn;
    if (get_id_by_username($username) != -1) {
        if (password_verify($password, $conn->query("SELECT password FROM user WHERE username='{$username}';")->fetch_assoc()["password"])) {
            return array(true, "登陆成功");
        } else {
            return array(false, "密码错误");
        }
    } else {
        return array(false, "用户不存在");
    }
}

function get_time()
{
    #date_default_timezone_set('Europe/London');
    return date('Y-m-d H:i:s');
}

function isMobile(): bool
{
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset($_SERVER['HTTP_VIA'])) {
        // 找不到为flase,否则为true
        return (bool)stristr($_SERVER['HTTP_VIA'], "wap");
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高。其中'MicroMessenger'是电脑微信
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel',
            'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi',
            'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile', 'MicroMessenger');
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') ===
                false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

function php_self()
{
    return substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1);
}

function alert($type,$message,$delay,$dest)
{
    switch ($type){
        case "success":
            $title = "成功";
            break;
        case "error":
            $title = "错误";
            break;
        case "warning":
            $title = "警告";
            break;
        case "info":
            $title = "信息";
            break;
        case "question":
            $title="请检查";
            break;
        default:
            $title = "";
            break;
        }
    echo "<script>Swal.fire({icon: '$type',title: '$title',text: '$message',timer:$delay,timerProgressBar: true});setTimeout(\"javascript:location.href='$dest'\", $delay);</script>";
}


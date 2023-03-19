<?php
// 应用公共文件
function alert($type, $message, $delay, $dest): string
{
    echo '
    <script src="/static/js/sweetalert2.all.min.js"></script>
    <link href="/static/css/sweetalert2.min.css" rel="stylesheet">
    ';
    switch ($type) {
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
            $title = "请检查";
            break;
        default:
            $title = "";
            break;
    }
    return "<script>window.onload = function() {
            Swal.fire({
            icon: '$type',
            title: '$title',
            text: '$message',
            timer:$delay,
            showConfirmButton: false,
            timerProgressBar: true});
            setTimeout(\"javascript:location.href='$dest'\", $delay);}
            </script>";
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
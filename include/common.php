<?php
header('Content-Type: text/html; charset=UTF-8');
include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
include("function.php");


//Enable error reporting
if ($Sys_config["debug"]) {
    ini_set("display_errors", "On");
    error_reporting(E_ALL);
}

try{
    $conn = new PDO("mysql:host={$Sys_config["db_host"]};dbname={$Sys_config["db_database"]};", $Sys_config["db_user"], $Sys_config["db_password"]);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // 禁用prepared statements的模拟效果
    $conn->exec("set names utf8"); //设置编码
} catch (PDOException $e) {
    die("数据库连接失败，错误信息：" . $e->getMessage());
}

//检查php_self()是否可用
if (php_self() == "") {
    die("无法获取php文件名，请检查php.ini中的cgi.fix_pathinfo是否为1");
}

//Initialize session
session_start();
if (!isset($_SESSION["isLogin"])) {
    $_SESSION["isLogin"] = false;
}

include($_SERVER['DOCUMENT_ROOT'] . "/include/user.php");
include($_SERVER['DOCUMENT_ROOT'] . "/include/account.php");
include($_SERVER['DOCUMENT_ROOT'] . "/include/sharepage.php");

//Initialize CSS
echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/resources/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/resources/icons/bootstrap-icons.css">
    <link href=”https://fonts.googleapis.com/icon?family=Material+Icons” rel=”stylesheet”>
    <script src="/resources/js/bootstrap.bundle.min.js"></script>
    <script src="/resources/js/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="/resources/css/sweetalert2.min.css">
    <script src="/resources/js/clipboard.min.js"></script>
</head>
';
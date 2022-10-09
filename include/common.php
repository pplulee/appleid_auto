<?php
header('Content-Type: text/html; charset=UTF-8');
include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
include("function.php");


//Enable error reporting
if ($Sys_config["debug"]) {
    ini_set("display_errors", "On");
    error_reporting(E_ALL);
}

if (!isset($conn)) {
    $conn = @mysqli_connect($Sys_config["db_host"], $Sys_config["db_user"], $Sys_config["db_password"], $Sys_config["db_database"]);  //数据库连接
    if (!$conn) {
        die("数据库连接失败：" . mysqli_connect_error());
    }
}

//Initialize session
session_start();
if (!isset($_SESSION["isLogin"])) {
    $_SESSION["isLogin"] = false;
}

include($_SERVER['DOCUMENT_ROOT'] . "/include/user.php");
include($_SERVER['DOCUMENT_ROOT'] . "/include/task.php");
include($_SERVER['DOCUMENT_ROOT'] . "/include/account.php");

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
</head>
';
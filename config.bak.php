<?php
$Sys_config["debug"] = true;
$Sys_config["enable_register"] = true;
$Sys_config["db_host"] = "localhost";
$Sys_config["db_user"] = "root";
$Sys_config["db_password"] = "123456";
$Sys_config["db_database"] = "appleid_auto";

$Sys_config["apiurl"] = "http://xxx.xxx"; // 站点地址，无需斜杠结尾
$Sys_config["apikey"] = "114514"; // API密钥
$Sys_config["webdriver_url"] = "http://"; // webdriver地址，需要带端口
$Sys_config["webdriver_proxy"] = ""; // webdriver代理设置，留空则不启用。支持无验证的http代理和socks5代理

// 是否启用Telegram Bot. 用于通知账号解锁情况. 留空则不启用
$Sys_config["telegram_bot_token"] = "";
$Sys_config["telegram_bot_chatid"] = "";
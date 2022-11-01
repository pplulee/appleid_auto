<?php
$Sys_config["debug"] = $_ENV["DEBUG"] ?? true;
$Sys_config["enable_register"] = $_ENV["ENABLE_REGISTER"] ?? true;
$Sys_config["db_host"] = $_ENV["DB_HOST"] ?? "localhost";
$Sys_config["db_user"] = $_ENV["DB_USER"] ?? "root";
$Sys_config["db_password"] = $_ENV["DB_PASS"] ?? "123456";
$Sys_config["db_database"] = $_ENV["DB_NAME"] ?? "appleid_auto";

$Sys_config["apiurl"] = $_ENV["API_URL"] ?? "http://apple"; # 站点地址，无需斜杠结尾
$Sys_config["apikey"] = $_ENV["API_KEY"] ?? "YOUR_API_KEY_HERE"; # API密钥
$Sys_config["backend_step_sleep"] = $_ENV["BACKEND_STEP_SLEEP"] ?? 3; # 后端脚本步骤执行间隔，单位秒
$Sys_config["webdriver_url"] = $_ENV["WEBDRIVE_URL"] ?? "http://webdrive:4444";

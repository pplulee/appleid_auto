<?php

use app\AppService;
use app\service\AccountService;
use app\service\AuthService;
use app\service\BackendService;
use app\service\ManualUnlockService;
use app\service\ProxyService;
use app\service\ShareService;

// 系统服务定义文件
// 服务在完成全局初始化之后执行
return [
    AppService::class,
    AuthService::class,
    AccountService::class,
    BackendService::class,
    ProxyService::class,
    ShareService::class,
    ManualUnlockService::class
];

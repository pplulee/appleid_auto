<?php

use app\AppService;
use app\service\AccountService;
use app\service\ApiService;
use app\service\AuthService;
use app\service\BackendService;
use app\service\UnlockService;
use app\service\ProxyService;
use app\service\ShareService;
use app\service\UserService;

// 系统服务定义文件
// 服务在完成全局初始化之后执行
return [
    AppService::class,
    AuthService::class,
    AccountService::class,
    BackendService::class,
    ProxyService::class,
    ShareService::class,
    UserService::class,
    UnlockService::class,
    ApiService::class
];

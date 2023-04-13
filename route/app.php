<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use app\middleware\Admin;
use app\middleware\Api;
use app\middleware\Auth;
use app\middleware\Share;
use app\middleware\UserIndex;
use think\facade\Route;


// 注册index
Route::rule('/', 'index/index');
Route::get('index', 'index/index');

// 注册用户登录部分服务
Route::group('user', function () {
    Route::post('login', 'user/login');
    Route::post('register', 'user/register');
})->middleware(Auth::class);


// 注册用户中心
Route::rule('user/', 'user/index')->middleware(UserIndex::class);
Route::group('user', function () {
    Route::get('/', 'user/index');
    Route::get('index', 'user/index');
    Route::get('info', 'user/info');
    Route::post('info', 'user/updateUser');
    // 账号相关
    Route::get('account/add', 'user/accountAdd');
    Route::post('account/add', 'user/accountUpdate');
    Route::get('account/:id/restart', 'user/accountRestart');
    Route::get('account/:id', 'user/accountEdit');
    Route::post('account/:id', 'user/accountUpdate');
    Route::delete('account/:id', 'user/accountDelete');
    Route::get('account', 'user/account');
    // 分享页相关
    Route::get('share/add', 'user/shareAdd');
    Route::post('share/add', 'user/shareUpdate');
    Route::get('share/:id', 'user/shareEdit');
    Route::post('share/:id', 'user/shareUpdate');
    Route::delete('share/:id', 'user/shareDelete');
    Route::get('share', 'user/share');
    // 代理相关
    Route::get('proxy/add', 'user/proxyAdd');
    Route::post('proxy/add', 'user/proxyUpdate');
    Route::get('proxy/:id', 'user/proxyEdit');
    Route::post('proxy/:id', 'user/proxyUpdate');
    Route::delete('proxy/:id', 'user/proxyDelete');
    Route::get('proxy', 'user/proxy');
    // 任务记录
    Route::get('record', 'user/unlockRecord');
    //
    Route::get('logout', 'user/logout');
})->middleware(UserIndex::class);


// 注册分享页API
Route::rule('shareapi/:link/[:password]', 'api/getSharepage');
// 注册分享页
Route::rule('share/', 'share/index')->middleware(Share::class);
Route::group('share', function () {
    Route::get('/', 'share/index');
    Route::get('/:link/:id/unlock', 'share/manualUnlock');
    Route::rule('/:link', 'share/index');
})->middleware(Share::class);

// 注册管理面板
Route::rule('admin/', 'admin/index')->middleware(Admin::class);
Route::group('admin', function () {
    Route::get('/', 'admin/index');
    Route::get('index', 'admin/index');
    Route::post('info', 'admin/updateUser');
    // 账号相关
    Route::get('account/:id/restart', 'admin/accountRestart');
    Route::post('account/add', 'admin/accountUpdate');
    Route::get('account/:id', 'admin/accountEdit');
    Route::post('account/:id', 'admin/accountUpdate');
    Route::delete('account/:id', 'admin/accountDelete');
    Route::get('account', 'admin/account');
    // 用户相关
    Route::post('user/add', 'admin/userUpdate');
    Route::get('user/:id', 'admin/userEdit');
    Route::post('user/:id', 'admin/userUpdate');
    Route::delete('user/:id', 'admin/userDelete');
    Route::get('user', 'admin/user');
    // 分享页相关
    Route::post('share/add', 'admin/shareUpdate');
    Route::get('share/:id', 'admin/shareEdit');
    Route::post('share/:id', 'admin/shareUpdate');
    Route::delete('share/:id', 'admin/shareDelete');
    Route::get('share', 'admin/share');
    // 代理相关
    Route::post('proxy/add', 'admin/proxyUpdate');
    Route::get('proxy/:id', 'admin/proxyEdit');
    Route::post('proxy/:id', 'admin/proxyUpdate');
    Route::delete('proxy/:id', 'admin/proxyDelete');
    Route::get('proxy', 'admin/proxy');
    // 任务记录
    Route::get('record', 'admin/unlockRecord');
})->middleware(Admin::class);

// 注册API
Route::group('api', function () {
    Route::rule('get_task_list', 'api/getTaskList');
    Route::rule('get_task_info', 'api/getTaskInfo');
    Route::rule('update_account', 'api/updateAccount');
    Route::rule('get_password', 'api/getPassword');
    Route::rule('check_api', 'api/checkApi');
    Route::rule('random_sharepage_password', 'api/randomSharePagePassword');
    Route::rule('report_proxy_error', 'api/reportProxyError');
    Route::rule('get_backend_api', 'api/getBackendApi');
    Route::rule('disable_account', 'api/disableAccount');
})->middleware(Api::class);
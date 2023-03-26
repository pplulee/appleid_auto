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
use app\middleware\Auth;
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


// 注册userindex
Route::rule('user/', 'user/index')->middleware(UserIndex::class);
Route::group('user', function () {
    Route::get('/', 'user/index');
    Route::get('index', 'user/index');
    Route::get('info', 'user/info');
    Route::post('info', 'user/updateUser');
    // 账号相关
    Route::get('account/add', 'user/accountAdd');
    Route::post('account/add', 'user/accountUpdate');
    Route::get('account/:id/unlock', 'user/accountUnlock');
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
    //
    Route::get('logout', 'user/logout');
})->middleware(UserIndex::class);
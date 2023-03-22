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
    Route::get('account/add', 'user/accountAdd');
    Route::post('account/add', 'user/accountUpdate');
    Route::get('account/:id', 'user/accountEdit');
    Route::post('account/:id', 'user/accountUpdate');
    Route::delete('account/:id', 'user/accountDelete');
    Route::get('account', 'user/account');
    Route::get('logout', 'user/logout');
})->middleware(UserIndex::class);
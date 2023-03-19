<?php
// 全局中间件定义文件
use think\middleware\SessionInit;

return [
    // 全局请求缓存
    // \think\middleware\CheckRequestCache::class,
    // 多语言加载
    // \think\middleware\LoadLangPack::class,
    // Session初始化
    SessionInit::class
];

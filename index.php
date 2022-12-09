<?php
include "include/common.php";
?>
<!DOCTYPE HTML>
<!--
    Dimension by HTML5 UP
    html5up.net | @ajlkn
    Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html lang="zh-cn">
<head>
    <meta charset="utf-8"/>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
    <link rel="stylesheet" href="/resources/css/main.css"/>
    <noscript>
        <link rel="stylesheet" href="/resources/css/noscript.css"/>
    </noscript>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>
    <title>AppleID托管</title>
</head>

<body>

<div id="wrapper">
    <header id="header">
        <div class="logo">
            <span class="icon fa-clipboard-check"></span>
        </div>
        <div class="content">
            <div class="inner">
                <h1>AppleID 自动化管理</h1>
                <p>管理AppleID的新方式</p>
            </div>
        </div>
        <nav>
            <ul>
                <li><a href="#intro">简介</a></li>
                <li><a href="#login">登录</a></li>
                <?php if ($Sys_config["enable_register"]) echo "<li><a href='#register'>注册</a></li>" ?>
            </ul>
        </nav>
    </header>
    <div id="main">
        <article id="intro">
            <h2 class="major">简介</h2>
            <p>基于密保问题，自动解锁Apple ID，自动关闭双重认证，提供前端账号展示，支持多账号</p>
        </article>
        <article id="login">
            <?php
            if (isset($_SESSION['isLogin']) and $_SESSION['isLogin']) {
                alert("warning","您已登录，自动跳转到用户界面！",1000, "userindex.php");
                exit;
            }
            ?>
            <h2 class="major">登录</h2>
            <form action="login.php" method="post">
                <div class="field half first">
                    <label for="username">用户名</label>
                    <input type="text" name="username" id="username" placeholder="请输入用户名"/>
                </div>
                <div class="field half">
                    <label for="password">密码</label>
                    <input type="password" name="password" id="password" placeholder="请输入密码"/>
                </div>
                <ul class="actions">
                    <li><input type="submit" value="登录" class="primary special" name="login"/></li>
                </ul>
            </form>
        </article>
        <article id="register">
            <?php
            if (isset($_SESSION['isLogin']) and $_SESSION['isLogin']) {
                alert("warning","您已登录，自动跳转到用户界面！",1000, "userindex.php");
                exit;
            }
            ?>
            <h2 class="major">注册</h2>
            <form action="register.php" method="post">
                <div class="field half first">
                    <label for="username">用户名</label>
                    <input type="text" name="username" id="username" placeholder="请输入用户名"/>
                </div>
                <div class="field half">
                    <label for="password">密码</label>
                    <input type="password" name="password" id="password" placeholder="请输入密码"/>
                </div>
                <ul class="actions">
                    <li><input type="submit" value="注册" class="primary special" name="register"/></li>
                </ul>
            </form>
        </article>
    </div>
    <?php include "footer.php"; ?>
</div>
<div id="bg"></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.3/jquery.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/skel/3.0.1/skel.min.js"></script>
<script src="/resources/js/util.js"></script>
<script src="/resources/js/main.js"></script>
<script>
    $(function () {
        $(window).load(function () {
            NProgress.done();
        });
        NProgress.set(0.0);
        NProgress.configure({showSpinner: false});
        NProgress.configure({minimum: 0.4});
        NProgress.configure({easing: 'ease', speed: 1200});
        NProgress.configure({trickleSpeed: 200});
        NProgress.configure({trickleRate: 0.2, trickleSpeed: 1200});
        NProgress.inc();
        $(window).ready(function () {
            NProgress.start();
        });
    });
</script>
</body>
</html>
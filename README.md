<h1 align="center">Apple ID 一键解锁工具</h1>
<p align="center">
    <a href="https://github.com/pplulee/appleid_auto/issues" style="text-decoration:none">
        <img src="https://img.shields.io/github/issues/pplulee/appleid_auto.svg" alt="GitHub issues"/>
    </a>
    <a href="https://github.com/pplulee/appleid_auto/stargazers" style="text-decoration:none" >
        <img src="https://img.shields.io/github/stars/pplulee/appleid_auto.svg" alt="GitHub stars"/>
    </a>
    <a href="https://github.com/pplulee/appleid_auto/network" style="text-decoration:none" >
        <img src="https://img.shields.io/github/forks/pplulee/appleid_auto.svg" alt="GitHub forks"/>
    </a>
    <a href="https://github.com/pplulee/apple_auto/blob/main/LICENSE" style="text-decoration:none" >
        <img src="https://img.shields.io/github/license/pplulee/appleid_auto" alt="GitHub license"/>
    </a>
</p>
<h3 align="center">请仔细阅读本文档以及未来我们会推出的 Wiki 文档，再使用。</h3>  
<h3 align="center">本项目仍在更新当中。</h3>

# 基本简介

“以全新方式管理你的 Apple ID” —— 这是一款基于密保问题的自动化 Apple ID 检测&解锁程序。

前端用于管理账号，支持添加多个账号，并提供展示账号页面；

后端定时检测账号是否被锁定，若被锁定或开启二步验证则自动解锁，修改密码并向API回报密码。

### 注意事项：

1. 目前**后端运行基于docker**，请确保机器已安装docker；
2. unblocker_manager为**后端管理程序**，会定时从API获取任务列表并部署docker容器（每个task对应一个容器）；
3. 程序**需要使用Chrome webdriver**，推荐使用Docker版 [selenium/standalone-chrome](https://hub.docker.com/r/selenium/standalone-chrome)，使用方法请自行寻找。

# 问题反馈&交流
开发者水平和能力有限，程序可能存在诸多bug，欢迎提出 Issue 或 Pull Request ，也欢迎各位大佬加入项目！
Telegram群：[@appleunblocker](https://t.me/appleunblocker)

# 使用方法
提供php环境和docker环境的部署方法，二选一即可。
## 使用PHP环境部署
**使用前请确保已部署好 Webdriver**

网页端运行环境推荐 php7.4 & MySQL8.0

1. 从Release下载网页源码并部署，导入数据库 (`db.sql`) 并修改配置文件 (`config.php`)（记得设置远程Webdriver地址） \
    默认账户：`admin` 密码：`admin`
2. 登录网站后，添加Apple账号，填写账号信息
3. 前往面板中任务列表，创建账号对应的解锁任务
4. 部署`backend\unblocker_manager.py`（提供一键部署交脚本，请见下方）
5. 查看`unblocker_manager`是否成功获取到任务列表
6. 查看容器是否部署并正常运行

`config.php` **正确示例**

```php
<?php
$Sys_config["debug"] = true;
$Sys_config["enable_register"] = true;
$Sys_config["db_host"] = "192.168.50.1:3306";
$Sys_config["db_user"] = "root";
$Sys_config["db_password"] = "password";
$Sys_config["db_database"] = "appleid_auto";

$Sys_config["apiurl"] = "http://192.168.50.1:80"; # 站点地址，无需斜杠结尾
$Sys_config["apikey"] = "password"; # API密钥
$Sys_config["backend_step_sleep"] = 3; # 后端脚本步骤执行间隔，单位秒
$Sys_config["webdriver_url"] = "http://selenium:4444";
```

### 一键部署unblocker_manager：
`wget https://raw.githubusercontent.com/pplulee/appleid_auto/main/backend/scheduling/install_unblocker.sh && bash install_unblocker.sh`
### 关于密保问题的说明：
问题一栏仅需填写关键词即可，例如”生日“、”工作“等，但请注意账号**安全问题的语言**

## 使用docker部署
`docker compose`和原生二选一即可
### 使用docker compose
更简单，只需要确保你本地安装了`docker`和`docker compose`

根据你的需要修改`docker-compose.yml`文件，然后在根目录执行`docker compose up -d`，等待所有容器上线,不要忘了数据导入

现有的`docker compose`文件包含了数据库，解锁工具，调度器和 `webdrive`，数据库端口`3306`，解锁工具端口`8080`，`webdrive`端口`4444`，数据库默认账号和密码都是`root`，使用此方法后端无需单独部署

需要注意的是，因为容器内部互相隔离，所以`IP`不能填`127.0.0.1`或者`localhost`

### 使用原生docker
请参考`docker compose`文件的每一个`service`进行拆分，环境变量在该文件中都有说明。Dockerfile分别在根目录和`backend`的两个子目录

# 文件说明
- `backend\unblocker_manager.py` 后端管理程序 \
说明：用于定时从API获取任务列表，并部署task对应的docker容器 \
启动参数：`-api_url <API地址> -api_key <API key> ` （API地址格式为http://xxx.xxx 末尾不需要加斜杠和路径）
- `backend\unlocker\main.py` 后端解锁程序 \
说明：通过Webdriver实现账号改密解锁，并向API提交新密码。**该程序依赖API运行** \
启动参数：`-api_url <API地址> -api_key <API key> -taskid <Task ID>`

仅部署**后端管理程序**即可，该脚本会自动从API站点获取任务并部署容器，默认同步时间为10分钟（手动同步可重启服务） \
若不想使用自动同步，也可以直接部署**后端解锁程序**，docker版 [sahuidhsu/appleid_auto](https://hub.docker.com/r/sahuidhsu/appleid_auto)


# API说明
等待添加……

# TODO List
- [x] 自动识别验证码
- [x] 检测账号被锁
- [x] 检测二步验证
- [ ] 检查密码正确
- [ ] 删除设备
- [x] 修改密码
- [x] 上报密码
- [x] Telegram Bot通知

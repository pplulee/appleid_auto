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

支持创建包含多个账号的分享页面，并可以为分享页面设置密码。

后端定时检测账号是否被锁定，若被锁定或开启二步验证则自动解锁，修改密码并向API回报密码。

自动删除Apple ID中的设备。

### 注意事项：

1. 目前**后端运行基于docker**，请确保机器已安装docker；
2. unblocker_manager为**后端管理程序**，会定时从API获取任务列表并部署docker容器（每个账号对应一个容器）；
3. 程序**需要使用Chrome webdriver**
   ，推荐使用Docker版 [selenium/standalone-chrome](https://hub.docker.com/r/selenium/standalone-chrome)
   ，docker部署指令如下，请根据需求修改参数。

```bash
docker run -d --name=webdriver --log-opt max-size=1m --log-opt max-file=1 --shm-size="2g" --restart=always -e SE_NODE_MAX_SESSIONS=10 -e SE_NODE_OVERRIDE_MAX_SESSIONS=true -e SE_SESSION_RETRY_INTERVAL=1 -e SE_VNC_VIEW_ONLY=1 -p 4444:4444 -p 5900:5900 selenium/standalone-chrome
```

# 问题反馈&交流

开发者水平和能力有限，程序可能存在诸多bug，欢迎提出 Issue 或 Pull Request ，也欢迎各位大佬加入项目！
Telegram群：[@appleunblocker](https://t.me/appleunblocker)

# 前端更新

从Release下载网页源码并覆盖原有文件，重新填写config.php，导入更新的数据库文件（开头为update_的文件）即可。

# 后端更新

若是最新版本的后端管理脚本，只需重启appleauto服务即可。若无法更新，可重新执行安装脚本

# 使用方法

**使用前请确保已部署好 Webdriver**
网页端运行环境推荐 php7.4 & MySQL8.0，理论支持MySQL5.x，未测试其他版本php环境。

1. 从Release下载网页源码并部署，导入数据库 (`sql/db.sql`) ，复制配置文件`config.bak.php`到`config.php`
   并修改（记得设置远程Webdriver地址） \
   默认账户：`admin` 密码：`admin`
2. 登录网站后，添加Apple账号，填写账号信息，以及检测间隔
3. 部署`backend\unblocker_manager.py`（提供一键部署脚本，请见下方）
4. 查看`unblocker_manager`是否成功获取到任务列表
5. 查看容器是否部署并正常运行

`config.php` **填写示例仅供参考，非所有配置**

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
$Sys_config["webdriver_url"] = "http://selenium:4444";
```

### 一键部署unblocker_manager（后端+webdriver）：

```bash
wget https://raw.githubusercontent.com/pplulee/appleid_auto/main/backend/install_unblocker.sh -O install_unblocker.sh && bash install_unblocker.sh
```

### 关于密保问题的说明：

问题一栏仅需填写关键词即可，例如”生日“、”工作“等，但请注意账号**安全问题的语言**

# 文件说明

- `backend\unblocker_manager.py` 后端管理程序 \
  说明：用于定时从API获取任务列表，并部署task对应的docker容器 \
  启动参数：`-api_url <API地址> -api_key <API key> ` （API地址格式为http://xxx.xxx 末尾不需要加斜杠和路径）
- `backend\unlocker\main.py` 后端解锁程序 \
  说明：通过Webdriver实现账号改密解锁，并向API提交新密码。**该程序依赖API运行** \
  启动参数：`-api_url <API地址> -api_key <API key> -taskid <Task ID>`

仅部署**后端管理程序**即可，该脚本会自动从API站点获取任务并部署容器，默认同步时间为10分钟（手动同步可重启服务） \
若不想使用自动同步，也可以直接部署**后端解锁程序**
，docker版 [sahuidhsu/appleid_auto](https://hub.docker.com/r/sahuidhsu/appleid_auto)

---

# API说明

路径： `/api/` \
方法： `GET` \
所有action均需要传入`key`参数，值为`config.php`中的`apikey` \
返回类型： `JSON` \
通用返回参数 


| 参数        | 值/类型             | 说明      |
|-----------|------------------|---------|
| `status`  | `success`/`fail` | 操作成功/失败 |
| `message` | `String`         | 提示信息    |

Action: `random_sharepage_password` \
说明： 生成随机分享页密码 \
输入参数：

| 参数       | 值/类型                        | 说明    |
|----------|-----------------------------|-------|
| `action` | `random_sharepage_password` | 操作    |
| `id`     | `Int`                       | 分享页ID |

返回参数：

| 参数         | 值/类型     | 说明  |
|------------|----------|-----|
| `password` | `String` | 新密码 |



……其余等待添加

# TODO List

- [x] 自动识别验证码
- [x] 检测账号被锁
- [x] 检测二步验证
- [x] 分享页面支持多个账号
- [x] 分享页可开启密码
- [x] 检查密码正确
- [x] 删除设备
- [x] 修改密码
- [x] 上报密码
- [x] Telegram Bot通知

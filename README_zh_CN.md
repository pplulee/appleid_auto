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
<h3 align="center">中文文档 | <a href="README.md">English</a> </h3>
<h3 align="center">请仔细阅读本文档以及未来我们会推出的 Wiki 文档，再使用。</h3>  
<h3 align="center">本项目仍在更新当中。</h3>

# 基本简介

“以全新方式管理你的 Apple ID” —— 这是一款基于密保问题的自动化 Apple ID 检测&解锁程序。

前端用于管理账号，支持添加多个账号，并提供展示账号页面；

支持创建包含多个账号的分享页面，并可以为分享页面设置密码。

后端定时检测账号是否被锁定，若被锁定或开启二步验证则自动解锁，修改密码并向API回报密码。

登录Apple ID并自动删除Apple ID中的设备。

启用代理池和Selenium集群，提高解锁成功率，防止风控。

### 注意事项：

1. 目前**后端运行基于docker**，请确保机器已安装docker；
2. unblocker_manager为**后端管理程序**，会定时从API获取任务列表并部署docker容器（每个账号对应一个容器）；
3. 程序**需要使用Chrome webdriver**
   ，推荐使用Docker版 [selenium/standalone-chrome](https://hub.docker.com/r/selenium/standalone-chrome)
   ，docker部署指令如下，请根据需求修改参数。(仅支持x86_64，如您有ARM需求
   ，请尝试[seleniarm/standalone-chromium](https://hub.docker.com/r/seleniarm/standalone-chromium) 或使用docker集群: [sahuidhsu/selenium-grid-docker](https://github.com/sahuidhsu/selenium-grid-docker))
```bash
docker run -d --name=webdriver --log-opt max-size=1m --log-opt max-file=1 --shm-size="2g" --restart=always -e SE_NODE_MAX_SESSIONS=10 -e SE_NODE_OVERRIDE_MAX_SESSIONS=true -e SE_SESSION_RETRY_INTERVAL=1 -e SE_VNC_VIEW_ONLY=1 -p 4444:4444 -p 5900:5900 selenium/standalone-chrome
```
4. 程序的**后端输出**当前支持三种语言：简体中文/英文/越南语，通过[使用方法](#使用方法)中的一键部署脚本可以方便地选择部署语言。


# 使用方法

**请先部署好前端，再安装后端。后端安装脚本提供一键安装webdriver** \
如果你想了解Selenium Grid集群，请前往 [sahuidhsu/selenium-grid-docker](https://github.com/sahuidhsu/selenium-grid-docker) \
网页端运行环境推荐 php7.4 & MySQL8.0，理论支持MySQL5.x，其他版本php可能不支持。

1. 从Release下载网页源码并部署，导入数据库 (`sql/db.sql`) ，复制配置文件`config.bak.php`到`config.php`，并填写设置项 \
   默认账户：`admin` 密码：`admin`
2. 登录网站后，添加Apple账号，填写账号信息
3. 部署`backend\unblocker_manager.py`（提供一键部署脚本，请见下方）
4. 查看`unblocker_manager`是否成功获取到任务列表
5. 查看容器是否部署并正常运行

### [推荐]一键部署unblocker_manager（后端+webdriver）：
```bash
bash <(curl -Ls https://raw.githubusercontent.com/pplulee/appleid_auto/main/backend/install_unblocker.sh)
```

### 关于密保问题的说明：

问题一栏仅需填写关键词即可，例如”生日“、”工作“等，但请注意账号安全问题的**语言**

# 前端更新

从Release下载网页源码并覆盖原有文件，重新填写config.php，导入更新的数据库文件（开头为update_的文件）即可。

# 后端更新

若是最新版本的后端管理脚本，只需重启appleauto服务即可。若无法更新，可重新执行安装脚本

# 问题反馈&交流

开发者水平和能力有限，程序可能存在诸多bug，欢迎提出 Issue 或 Pull Request ，也欢迎各位大佬加入项目！ \
Telegram群：[@appleunblocker](https://t.me/appleunblocker)

# 文件说明

- `backend\unblocker_manager.py` 后端管理程序 \
  说明：用于定时从API获取任务列表，并部署task对应的docker容器 \
  启动参数：`-api_url <API地址> -api_key <API key> ` （API地址格式为`http(s)://xxx.xxx` 末尾不需要加`/`或路径）
- `backend\unlocker\main.py` 后端解锁程序 \
  说明：通过Webdriver实现账号改密解锁，并向API提交新密码。**该程序依赖API运行** \
  启动参数：`-api_url <API地址> -api_key <API key> -taskid <Task ID>`

仅部署**后端管理程序**即可，该脚本会自动从API站点获取任务并部署容器，默认同步时间为10分钟（重启服务即可立即同步） \
若不想使用自动同步，也可以直接部署**后端解锁程序** ，docker版 [sahuidhsu/appleid_auto](https://hub.docker.com/r/sahuidhsu/appleid_auto)

---
# 请我喝杯可乐
[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/baiyimiao) \
USDT-TRC20: TV1su1RnQny27YEF9WG4DbC8AAz3udt6d4 \
ETH-ERC20：0xea8fbe1559b1eb4b526c3bb69285203969b774c5 \
【广告】如果您有使用邮局的需求，欢迎咨询[开发者](https://t.me/baiyimiao) (Telegram)

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

---

# JSON API接口

支持通过分享页面链接，以JSON方式获取账号信息，用于对接其他APP \
分享页面链接指页面的代码，并非整个URL

API地址：`/api/share.php` \
请求方法：`GET` \
输入参数：

| 参数           | 值/类型     | 说明                |
|--------------|----------|-------------------|
| `share_link` | `String` | 分享页代码             |
| `password`   | `String` | 分享页密码（若未设置密码则不需要） |

返回参数：

| 参数         | 值/类型             | 说明            |
|------------|------------------|---------------|
| `status`   | `success`/`fail` | 操作成功/失败       |
| `message`  | `String`         | 提示信息          |
| `accounts` | `Array`          | 账号信息列表（内容见下表） |

账号信息：

| 参数           | 值/类型     | 说明     |
|--------------|----------|--------|
| `id`         | `Int`    | 账号ID   |
| `username`   | `String` | 账号     |
| `password`   | `String` | 密码     |
| `status`     | `Bool`   | 账号状态   |
| `last_check` | `String` | 上次检查时间 |
| `remark`     | `String` | 账号前端备注 |


---
# TODO List

- [x] 自动识别验证码
- [x] 检测账号被锁
- [x] 检测二步验证
- [x] 分享页面支持多个账号
- [x] 分享页可开启密码
- [x] 检查密码正确
- [x] 删除设备
- [x] 定时修改密码
- [x] 上报密码
- [x] 代理池
- [x] Telegram Bot通知
- [x] JSON API接口获取账号信息
- [x] 后端支持从API接口获取代理
- [ ] 分享页支持有效期

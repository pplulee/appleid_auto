# 简介
以全新方式管理你的 Apple ID

基于密保问题的自动化Apple ID检测&解锁程序程序

前端用于管理账号，支持添加多个账号，并提供展示账号页面

后端定时检测账号是否被锁定，若被锁定或开启二步验证则自动解锁，修改密码并向API回报密码

目前后端运行基于docker，请确保机器已安装docker

unblocker_manager为后端管理程序，会定时从API获取任务列表并部署docker容器（每个task对应一个容器）

**程序需要使用Chrome webdriver**，推荐使用Docker版 [selenium/standalone-chrome](https://hub.docker.com/r/selenium/standalone-chrome)，使用方法请见官方教程

# 使用方法
1. 部署前端网页，导入数据库并修改配置文件 \
    默认账户：`admin` 密码：`admin`
2. 登录网站后，添加Apple账号，填写账号信息
3. 前往任务列表创建任务
4. 部署`backend\unblocker_manager.py`（启动参数见下方）
5. 查看`unblocker_manager`是否成功获取到任务列表
6. 查看容器是否部署并正常运行

# 文件说明
- `backend\unblocker_manager.py` 后端管理程序 \
说明：用于定时从API获取任务列表，并部署task对应的docker容器 \
启动参数：`-api_url <API地址> -api_key <API key> ` （API地址格式为http://xxx.xxx 末尾不需要加后缀和斜杠）
- `backend\unlocker\main.py` 后端解锁程序 \
说明：通过Webdriver实现账号改密解锁，并向API提交新密码。**该程序必须使用API运行** \
启动参数：`-api_url <API地址> -api_key <API key> -taskid <Task ID>`

# API说明
等待添加……

# TODO
- [x] 自动识别验证码
- [x] 检测账号被锁
- [x] 检测二步验证
- [ ] 检查密码正确
- [ ] 删除设备
- [x] 修改密码
- [x] 上报密码
- [x] Telegram Bot通知

# 问题反馈&交流
本人水平和能力有限，程序可能存在bug，欢迎提出issue或PR，也欢迎各位大佬加入项目 \
Telegram群：[@appleunblocker](https://t.me/appleunblocker)

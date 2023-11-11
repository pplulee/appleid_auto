#!/bin/bash

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
PLAIN='\033[0m'
BLUE="\033[36m"

echo "请选择语言 | Please select a language"
echo -e "${YELLOW}Please note that the language you choose will affect the output of the backend program"
echo -e "请注意，你选择的语言将影响后端程序的输出${PLAIN}"
echo -e "${BLUE}However no support for language other than Chinese and English is provided in this installation script${PLAIN}"
echo -e "${BLUE}但是本安装脚本仅提供中文和英文支持${PLAIN}"
echo "1.简体中文(zh_cn)"
echo "2.English(en_us)"
echo "3.Vietnamese(vi_vn)"
read -e language
if [ $language != "1" ] && [ $language != "2" ] && [ $language != "3" ]; then
    echo "输入错误，已退出 | Input error, exit"
    exit;
fi
if [ $language == '1' ]; then
  echo "以全新方式管理你的 Apple ID，基于密保问题的自动化Apple ID检测&解锁程序程序"
  echo "项目地址：github.com/pplulee/appleid_auto"
  echo "项目交流TG群：@appleunblocker"
  echo "==============================================================="
else
  echo "Manage your Apple ID in a new way, an automated Apple ID detection & unlocking program based on security questions"
  echo "Project address: github.com/pplulee/appleid_auto"
  echo "Project discussion Telegram group: @appleunblocker"
  echo "==============================================================="
fi
if docker >/dev/null 2>&1; then
    echo "Docker已安装 | Docker is installed"
else
    echo "Docker未安装，开始安装…… | Docker is not installed, start installing..."
    docker version > /dev/null || curl -fsSL get.docker.com | bash
    systemctl enable docker && systemctl restart docker
    echo "Docker安装完成 | Docker installed"
fi
if [ $language == '1' ]; then
  echo "开始安装Apple_Auto后端"
  echo "请输入API URL（前端域名，格式 http[s]://xxx.xxx）"
  read -e api_url
  echo "请输入API Key"
  read -e api_key
  echo "是否启用自动更新？(y/n)"
  read -e auto_update
  echo "请输入任务同步周期(单位:分钟，默认15)"
  read -e sync_time
  if [ "$sync_time" = "" ]; then
      sync_time=15
  fi
  echo "是否部署Selenium Docker容器？(y/n)"
  read -e run_webdriver
else
  echo "Start installing Apple_Auto backend"
  echo "Please enter API URL (http://xxx.xxx)"
  read -e api_url
  echo "Please enter API Key"
  read -e api_key
  echo "Do you want to enable auto update? (y/n)"
  read -e auto_update
  echo "Please enter the task synchronization period (unit: minute, default 15)"
  read -e sync_time
  if [ "$sync_time" = "" ]; then
      sync_time=15
  fi
  echo "Do you want to deploy Selenium Docker container? (y/n)"
  read -e run_webdriver
fi
if [ "$run_webdriver" = "y" ]; then
    echo "开始部署Selenium Docker容器 | Start deploying Selenium Docker container"
    echo "请输入Selenium运行端口（默认4444） | Please enter Selenium running port (default 4444)"
    read -e webdriver_port
    if [ "$webdriver_port" = "" ]; then
        webdriver_port=4444
    fi
    echo "请输入Selenium最大会话数（默认10） | Please enter the maximum session number (default 10)"
    read -e webdriver_max_session
    if [ "$webdriver_max_session" = "" ]; then
        webdriver_max_session=10
    fi
    if docker ps -a --format '{{.Names}}' | grep -q '^webdriver$'; then
    docker rm -f webdriver
    fi
    docker pull selenium/standalone-chrome
    docker run -d --name=webdriver --log-opt max-size=1m --log-opt max-file=1 --shm-size="1g" --restart=always -e SE_NODE_MAX_SESSIONS=$webdriver_max_session -e SE_NODE_OVERRIDE_MAX_SESSIONS=true -e SE_SESSION_RETRY_INTERVAL=1 -e SE_START_VNC=false -p $webdriver_port:4444 selenium/standalone-chrome
    echo "Webdriver Docker容器部署完成 | Webdriver Docker container deployed"
fi
enable_auto_update=$([ "$auto_update" == "y" ] && echo True || echo False)
if docker ps -a --format '{{.Names}}' | grep -q '^appleauto$'; then
    docker rm -f appleauto
fi
docker pull sahuidhsu/appleauto_backend
docker run -d --name=appleauto --log-opt max-size=1m --log-opt max-file=2 --restart=always --network=host -e API_URL=$api_url -e API_KEY=$api_key -e SYNC_TIME=$sync_time -e AUTO_UPDATE=$enable_auto_update -e LANG=$language -v /var/run/docker.sock:/var/run/docker.sock sahuidhsu/appleauto_backend
if [ $language = "1" ]; then
  echo "安装完成，容器已启动"
  echo "默认容器名：appleauto"
  echo "操作方法："
  echo "停止容器：docker stop appleauto"
  echo "重启容器：docker restart appleauto"
  echo "查看容器日志：docker logs appleauto"
else
  echo "Installation completed, container started"
  echo "Default container name: appleauto"
  echo "Operation method:"
  echo "Stop: docker stop appleauto"
  echo "Restart: docker restart appleauto"
  echo "Check status: docker logs appleauto"
fi
exit 0
#!/bin/bash
install_path="/opt/apple_auto"
echo "请选择语言 | Please select language"
echo "1.简体中文"
echo "2.English"
read -e language
if [ $language != "1" ] && [ $language != "2" ]; then
    echo "输入错误，已退出 | Input error, exit"
    exit;
fi
if [ $language == '1' ]; then
  echo "以全新方式管理你的 Apple ID，基于密保问题的自动化Apple ID检测&解锁程序程序"
  echo "项目地址：github.com/pplulee/appleid_auto"
  echo "项目交流TG群：@appleunblocker"
  echo "使用时请确保本机已安装Python3.6+ pip3 Docker"
  echo "==============================================================="
else
  echo "Manage your Apple ID in a new way, an automated Apple ID detection & unlocking program based on security questions"
  echo "Project address: github.com/pplulee/appleid_auto"
  echo "Project discussion Telegram group: @appleunblocker"
  echo "Please make sure you have Python3.6+, pip3, Docker installed"
  echo "==============================================================="
fi
if python3 -V >/dev/null 2>&1; then
    echo "Python3已安装 | Python3 is installed"
    python_path=$(which python3)
    echo "Python3路径 | Python3 Path：$python_path"
else
    echo "Python3未安装，开始安装…… | Python3 is not installed, start installing..."
    if [ -f /etc/debian_version ]; then
        apt update && apt -y install python3 python3-pip
    elif [ -f /etc/redhat-release ]; then
        yum -y install python3 python3-pip
    else
       echo "无法检测到当前系统，已退出 | Unable to detect system package manager, exit"
       exit;
    fi
    python_path=$(which python3)
fi
if pip3 >/dev/null 2>&1; then
    echo "pip3已安装 | pip3 is installed"
else
    echo "pip3未安装，开始安装…… | pip3 is not installed, start installing..."
    if [ -f /etc/debian_version ]; then
        apt update && apt -y install python3-pip
    elif [ -f /etc/redhat-release ]; then
        yum -y install python3-pip
    else
       echo "无法检测到当前系统，已退出 | Unable to detect system package manager, exit"
       exit;
    fi
    echo "pip3安装完成 | pip3 installed"
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
  echo "请输入API URL（http://xxx.xxx）"
  read -e api_url
  echo "请输入API Key"
  read -e api_key
  echo "是否部署Selenium Docker容器？(y/n)"
  read -e run_webdriver
else
  echo "Start installing Apple_Auto backend"
  echo "Please enter API URL (http://xxx.xxx)"
  read -e api_url
  echo "Please enter API Key"
  read -e api_key
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
    docker pull selenium/standalone-chrome
    docker run -d --name=webdriver --log-opt max-size=1m --log-opt max-file=1 --shm-size="1g" --restart=always -e SE_NODE_MAX_SESSIONS=$webdriver_max_session -e SE_NODE_OVERRIDE_MAX_SESSIONS=true -e SE_SESSION_RETRY_INTERVAL=1 -e SE_VNC_VIEW_ONLY=1 -p $webdriver_port:4444 -p 5900:5900 selenium/standalone-chrome
    echo "Webdriver Docker容器部署完成 | Webdriver Docker container deployed"
fi
rm -rf install_unblocker
mkdir install_unblocker
cd install_unblocker
echo "开始下载文件…… | Start downloading files..."
wget https://raw.githubusercontent.com/pplulee/appleid_auto/EN_unblocker/backend/requirements.txt -O requirements.txt
wget https://raw.githubusercontent.com/pplulee/appleid_auto/EN_unblocker/backend/unblocker_manager.py -O unblocker_manager.py
SERVICE_FILE="[Unit]
Description=appleauto
Wants=network.target
[Service]
WorkingDirectory=$install_path
ExecStart=$python_path $install_path/unblocker_manager.py -api_url $api_url -api_key $api_key -lang $language
Restart=on-abnormal
RestartSec=5s
KillMode=mixed
[Install]
WantedBy=multi-user.target"
if [ ! -f "unblocker_manager.py" ];then
    echo "主程序文件不存在，请检查 | Main program file does not exist, please check"
    exit 1
fi
if [ ! -d "$install_path" ]; then
    mkdir "$install_path"
fi
pip3 install -r requirements.txt
cp -f unblocker_manager.py "$install_path"/unblocker_manager.py
if [ ! -f "/usr/lib/systemd/system/appleauto.service" ];then
    rm -rf /usr/lib/systemd/system/appleauto.service
fi
echo -e "${SERVICE_FILE}" > /lib/systemd/system/appleauto.service
systemctl daemon-reload
systemctl enable appleauto
systemctl restart appleauto
systemctl status appleauto
if [ $language = "1" ]; then
  echo "安装完成，服务已启动"
  echo "默认服务名：appleauto"
  echo "操作方法："
  echo "启动服务：systemctl start appleauto"
  echo "停止服务：systemctl stop appleauto"
  echo "重启服务：systemctl restart appleauto"
  echo "查看服务状态：systemctl status appleauto"
else
  echo "Installation completed, service started"
  echo "Default service name: appleauto"
  echo "Operation method:"
  echo "Start: systemctl start appleauto"
  echo "Stop: systemctl stop appleauto"
  echo "Restart: systemctl restart appleauto"
  echo "Check status: systemctl status appleauto"
fi
exit 0
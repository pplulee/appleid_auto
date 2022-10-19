#!/bin/bash
install_path="/opt/apple_auto"

echo "以全新方式管理你的 Apple ID，基于密保问题的自动化Apple ID检测&解锁程序程序"
echo "项目地址：github.com/pplulee/appleid_auto"
echo "项目交流TG群：@appleunblocker"
echo "使用时请确保本机已安装Python3.6+ pip3 Docker"
echo "==============================================================="
if python3 -V >/dev/null 2>&1; then
    echo "Python3已安装"
    python_path=$(which python3)
    echo "Python3路径：$python_path"
else
    echo "Python3未安装，开始安装……"
    if [ -f /etc/debian_version ]; then
        apt update && apt -y install python3 python3-pip
    elif [ -f /etc/redhat-release ]; then
        yum -y install python3 python3-pip
    else
       echo "无法检测到当前系统，已退出"
       exit;
    fi
fi
if pip3 >/dev/null 2>&1; then
    echo "pip3已安装"
else
    echo "pip3未安装，开始安装……"
    if [ -f /etc/debian_version ]; then
        apt update && apt -y install python3-pip
    elif [ -f /etc/redhat-release ]; then
        yum -y install python3-pip
    else
       echo "无法检测到当前系统，已退出"
       exit;
    fi
    echo "pip3安装完成"
fi
if docker >/dev/null 2>&1; then
    echo "Docker已安装"
else
    echo "Docker未安装，开始安装……"
    docker version > /dev/null || curl -fsSL get.docker.com | bash
    systemctl enable docker && systemctl restart docker
    echo "Docker安装完成"
fi
echo "开始安装Apple_Auto后端"
echo "请输入API URL（http://xxx.xxx）"
read -e api_url
echo "请输入API Key"
read -e api_key
mkdir install_unblocker
cd install_unblocker
echo "开始下载文件……"
wget https://raw.githubusercontent.com/pplulee/appleid_auto/main/backend/requirements.txt -O requirements.txt
wget https://raw.githubusercontent.com/pplulee/appleid_auto/main/backend/unblocker_manager.py -O unblocker_manager.py
SERVICE_FILE="[Unit]
Description=appleauto
Wants=network.target
[Service]
WorkingDirectory=$install_path
ExecStart=$python_path $install_path/unblocker_manager.py -api_url $api_url -api_key $api_key
Restart=on-abnormal
RestartSec=5s
KillMode=mixed
[Install]
WantedBy=multi-user.target"
if [ ! -f "unblocker_manager.py" ];then
    echo "主程序文件不存在，请检查"
    exit 1
fi
if [ ! -d "$install_path" ]; then
    mkdir "$install_path"
fi
pip3 install -r requirements.txt
cp unblocker_manager.py "$install_path"/unblocker_manager.py
if [ ! -f "/usr/lib/systemd/system/appleauto.service" ];then
    rm -rf /usr/lib/systemd/system/appleauto.service
fi
echo -e "${SERVICE_FILE}" > /lib/systemd/system/appleauto.service
systemctl daemon-reload
systemctl enable appleauto
systemctl restart appleauto
systemctl status appleauto
echo "默认服务名：appleauto"
echo "安装完成"
exit 0
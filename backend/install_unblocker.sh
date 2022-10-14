#!/bin/bash
install_path="/opt/apple_auto"

echo "以全新方式管理你的 Apple ID，基于密保问题的自动化Apple ID检测&解锁程序程序"
echo "项目地址：github.com/pplulee/appleid_auto"
echo "使用时请确保本机已安装Python3和Docker"
echo "==============================================================="
if python3 -V >/dev/null 2>&1; then
    echo "Python3已安装"
    python_path=$(which python3)
    echo "Python3路径：$python_path"
else
    echo "Python3未安装，请先安装Python3"
    exit 1
fi
if docker >/dev/null 2>&1; then
    echo "Docker已安装"
else
    echo "Docker未安装，请先安装Docker"
    exit 1
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
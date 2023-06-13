#!/bin/bash

# 停止appleauto服务
sudo systemctl stop appleauto

# 删除appleauto服务
sudo systemctl disable appleauto
sudo rm /lib/systemd/system/appleauto.service
sudo systemctl daemon-reload

# 安装新版
echo "开始安装Apple_Auto后端"
if docker >/dev/null 2>&1; then
    echo "Docker已安装 | Docker is installed"
else
    echo "Docker未安装，开始安装…… | Docker is not installed, start installing..."
    docker version > /dev/null || curl -fsSL get.docker.com | bash
    systemctl enable docker && systemctl restart docker
    echo "Docker安装完成 | Docker installed"
fi
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
enable_auto_update=$([ "$auto_update" == "y" ] && echo True || echo False)
docker run -d --name=appleauto --log-opt max-size=1m --log-opt max-file=2 --restart=always --network=host -e API_URL=$api_url -e API_KEY=$api_key -e SYNC_TIME=$sync_time -e AUTO_UPDATE=$enable_auto_update -e LANG=1 -v /var/run/docker.sock:/var/run/docker.sock sahuidhsu/appleauto_backend
echo "安装完成，容器已启动"
echo "默认服务名：appleauto"
echo "操作方法："
echo "停止容器：docker stop appleauto"
echo "重启容器：docker restart appleauto"
echo "查看容器日志：docker logs appleauto"
exit 0
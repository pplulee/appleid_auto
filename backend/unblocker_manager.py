import argparse
import json
import logging
import os
import platform
import time

import schedule
from requests import get

prefix = "apple-auto_"
parser = argparse.ArgumentParser(description="")
parser.add_argument("-api_url", help="API URL", required=True)
parser.add_argument("-api_key", help="API key", required=True)
args = parser.parse_args()
api_url = args.api_url
api_key = args.api_key


logger = logging.getLogger()
logger.setLevel('INFO')
BASIC_FORMAT = "%(asctime)s [%(levelname)s] %(message)s"
DATE_FORMAT = "%Y-%m-%d %H:%M:%S"
formatter = logging.Formatter(BASIC_FORMAT, DATE_FORMAT)
chlr = logging.StreamHandler()
chlr.setFormatter(formatter)
logger.addHandler(chlr)


class API:
    def __init__(self):
        self.url = api_url
        self.key = api_key

    def get_task_list(self):
        try:
            result = json.loads(get(f"{self.url}/api/?key={self.key}&action=get_task_list", verify=False).text)
        except Exception as e:
            logger.error("获取任务列表失败")
            return False
        else:
            if result['status'] == "fail":
                logger.error("获取任务列表失败")
                return False
            elif result['data'] == "":
                return []
            else:
                return result['data'].split(",")


class local_docker:
    def __init__(self, api):
        self.api = api
        self.local_list = self.get_local_list()

    def deploy_docker(self, id):
        logger.info(f"部署容器{id}")
        os.system(f"docker run -d --name={prefix}{id} \
        -e api_url={self.api.url} \
        -e api_key={self.api.key} \
        -e taskid={id} \
        --log-opt max-size=1m \
        --log-opt max-file=1 \
        --restart=on-failure \
        sahuidhsu/appleid_auto")

    def remove_docker(self, id):
        logger.info(f"删除容器{id}")
        os.system(f"docker stop {prefix}{id} && docker rm {prefix}{id}")

    def get_local_list(self):
        local_list = []
        result = os.popen("docker ps --format \"{{.Names}}\" -a")
        for line in result.readlines():
            if line.find(prefix) != -1:
                local_list.append(line.strip().split("_")[1])
        logger.info(f"本地存在{len(local_list)}个容器")
        return local_list

    def get_remote_list(self):
        result_list = self.api.get_task_list()
        if not result_list:
            logger.info("获取云端任务列表失败，使用本地列表")
            return self.local_list
        else:
            logger.info(f"从云端获取到{len(result_list)}个任务")
            return result_list

    def sync(self):
        logger.info("开始同步")
        self.local_list = self.get_local_list()
        # 处理需要删除的容器（本地存在，云端不存在）
        for id in self.local_list:
            if id not in self.get_remote_list():
                self.remove_docker(id)
                self.local_list.remove(id)
        # 处理需要部署的容器（本地不存在，云端存在）
        remote_list = self.get_remote_list()
        for id in remote_list:
            if id not in self.local_list:
                self.deploy_docker(id)
                self.local_list.append(id)
        logger.info("同步完成")

    def clean_local_docker(self):
        logger.info("开始清理本地容器")
        self.local_list = self.get_local_list()
        for name in self.local_list:
            self.remove_docker(name)
        logger.info("清理完成")

    def update(self):
        logger.info("开始检查更新")
        self.local_list = self.get_local_list()
        if len(self.local_list) == 0:
            logger.info("没有容器需要更新")
            return
        local_list_str = " ".join([f"{prefix}{id}" for id in self.local_list])
        os.system(f"docker run --rm \
        -v /var/run/docker.sock:/var/run/docker.sock \
        containrrr/watchtower \
        --cleanup \
        --run-once \
        {local_list_str}")


def job():
    global Local
    logger.info("开始定时任务")
    Local.sync()

def update():
    global Local
    logger.info("开始更新任务")
    Local.update()

logger.info("AppleAuto后端管理服务启动")
api = API()
Local = local_docker(api)
logger.info("拉取最新镜像")
os.system(f"docker pull sahuidhsu/appleid_auto")
logger.info("删除本地所有容器")
Local.clean_local_docker()
job()
schedule.every(10).minutes.do(job)
schedule.every().day.at("00:00").do(update)
while True:
    schedule.run_pending()
    time.sleep(1)

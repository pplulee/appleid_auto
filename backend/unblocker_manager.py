import argparse
import logging
import os
import threading
import time
from json import loads, dumps

import schedule
import urllib3
from flask import Flask, request
from requests import post

urllib3.disable_warnings()

prefix = "apple-auto_"
parser = argparse.ArgumentParser(description="")
parser.add_argument("-api_url", help="API URL", required=True)
parser.add_argument("-api_key", help="API key", required=True)
parser.add_argument('-lang', help='Language', default='1')
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

if args.lang == '1':
    language = 'zh_cn'
elif args.lang == '2':
    language = 'en_us'
elif args.lang == '3':
    language = 'vi_vn'
else:
    logger.error("语言参数错误，默认使用中文")
    language = 'zh_cn'


class API:
    def __init__(self):
        self.url = api_url
        self.key = api_key

    def get_backend_api(self):
        try:
            result = loads(
                post(f"{self.url}/api/get_backend_api",
                     verify=False,
                     headers={"key": self.key}).text)
        except Exception as e:
            logger.error("获取后端接口失败")
            logger.error(e)
            return {'enable': False}
        else:
            if result['status']:
                return result['data']
            else:
                logger.error("获取后端接口失败")
                logger.error(result['msg'])
                return {'enable': False}

    def get_task_list(self):
        try:
            result = loads(
                post(f"{self.url}/api/get_task_list",
                     verify=False,
                     headers={"key": self.key}).text)
        except Exception as e:
            logger.error("获取任务列表失败")
            logger.error(e)
            return False
        else:
            if result['status']:
                return result['data']
            else:
                logger.error("获取任务列表失败")
                logger.error(result['msg'])
                return None


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
        -e lang={language} \
        --restart=on-failure \
        --log-opt max-size=1m --log-opt max-file=2 \
        sahuidhsu/appleid_auto:2.0")

    def remove_docker(self, id):
        logger.info(f"删除容器{id}")
        os.system(f"docker stop {prefix}{id} && docker rm {prefix}{id}")

    def get_local_list(self):
        local_list = []
        result = os.popen("docker ps --format \"{{.Names}}\" -a")
        for line in result.readlines():
            if line.find(prefix) != -1:
                local_list.append(int(line.strip().split("_")[1]))
        logger.info(f"本地存在{len(local_list)}个容器")
        return local_list

    def restart_docker(self, id):
        logger.info(f"重启容器{id}")
        os.system(f"docker restart {prefix}{id}")

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
        remote_list = self.get_remote_list()
        # 处理需要删除的容器（本地存在，云端不存在）
        for id in self.local_list:
            if id not in remote_list:
                self.remove_docker(id)
                self.local_list.remove(id)
        # 处理需要部署的容器（本地不存在，云端存在）
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


def start_app(ip, port, token):
    logging.info("启动后端接口")
    app = Flask(__name__)

    @app.before_request
    def before_request():
        # 检测请求类型是和否为POST
        if request.method != 'POST':
            logging.error("请求类型错误")
            data = {'status': False, 'msg': '请求类型错误'}
            json_data = dumps(data).encode('utf-8')
            return app.response_class(json_data, mimetype='application/json')
        if 'token' not in request.headers:
            logging.error("请求头中未包含token")
            data = {'status': False, 'msg': '请求头中未包含token'}
            json_data = dumps(data).encode('utf-8')
            return app.response_class(json_data, mimetype='application/json')
        if request.headers['token'] != token:
            logging.error("密码错误")
            data = {'status': False, 'msg': 'token错误'}
            json_data = dumps(data).encode('utf-8')
            return app.response_class(json_data, mimetype='application/json')
        if 'id' not in request.form:
            logging.error("缺少任务id")
            data = {'status': False, 'msg': '缺少任务id'}
            json_data = dumps(data).encode('utf-8')
            return app.response_class(json_data, mimetype='application/json')

    @app.route('/addTask', methods=['POST'])
    def add_task():
        logging.info("收到设置任务请求")
        thread_add_task = threading.Thread(target=Local.deploy_docker, args=(request.form['id'],))
        thread_add_task.start()
        data = {'status': True, 'msg': '设置成功'}
        json_data = dumps(data).encode('utf-8')
        return app.response_class(json_data, mimetype='application/json')

    @app.route('/removeTask', methods=['POST'])
    def remove_task():
        logging.info("收到删除任务请求")
        thread_remove_task = threading.Thread(target=Local.remove_docker, args=(request.form['id'],))
        thread_remove_task.start()
        data = {'status': True, 'msg': '删除成功'}
        json_data = dumps(data).encode('utf-8')
        return app.response_class(json_data, mimetype='application/json')

    @app.route('/restartTask', methods=['POST'])
    def restart_task():
        logging.info("收到重启任务请求")
        thread_remove_task = threading.Thread(target=Local.restart_docker, args=(request.form['id'],))
        thread_remove_task.start()
        data = {'status': True, 'msg': '重启成功'}
        json_data = dumps(data).encode('utf-8')
        return app.response_class(json_data, mimetype='application/json')

    app.run(host=ip, port=port)


def main():
    logger.info("AppleAuto后端管理服务启动")
    api = API()
    backend_api_result = api.get_backend_api()
    global Local
    Local = local_docker(api)
    logger.info("拉取最新镜像")
    os.system(f"docker pull sahuidhsu/appleid_auto:2.0")
    logger.info("删除本地所有容器")
    os.system(f"docker stop $(docker ps -a |  grep \"{prefix}*\"  | awk '{{print $1}}')")
    os.system(f"docker rm $(docker ps -a |  grep \"{prefix}*\"  | awk '{{print $1}}')")
    if backend_api_result is not None and backend_api_result['enable']:
        thread_app = threading.Thread(target=start_app, daemon=True, args=(
            backend_api_result['listen_ip'], backend_api_result['listen_port'], backend_api_result['token']))
        thread_app.start()
    job()
    schedule.every(10).minutes.do(job)
    schedule.every().day.at("00:00").do(update)
    while True:
        schedule.run_pending()
        time.sleep(1)


if __name__ == '__main__':
    main()

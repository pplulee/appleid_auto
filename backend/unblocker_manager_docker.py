import argparse
import logging
import threading
import time
from json import loads, dumps

import docker
import schedule
import urllib3
from flask import Flask, request
from requests import post

urllib3.disable_warnings()

prefix = "apple-auto_"
image_name = "sahuidhsu/appleid_auto:2.0"
parser = argparse.ArgumentParser(description="")
parser.add_argument("-api_url", help="API URL", required=True)
parser.add_argument("-api_key", help="API key", required=True)
parser.add_argument("-sync_time", help="同步时间间隔", default="10")
parser.add_argument('-lang', help='Language', default='1')
parser.add_argument("-auto_update", help="启用自动更新镜像", action='store_true')
args = parser.parse_args()

api_url = args.api_url
api_key = args.api_key
sync_time = int(args.sync_time)
enable_auto_update = args.auto_update

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
client = docker.DockerClient(base_url='unix://var/run/docker.sock')


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
            if result['code'] == 200:
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
            if result['code'] == 200:
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
        try:
            container_name = f"{prefix}{id}"
            environment = {
                'api_url': self.api.url,
                'api_key': self.api.key,
                'taskid': id,
                'lang': language
            }
            restart_policy = {"Name": "on-failure"}
            log_config = docker.types.LogConfig(max_size="1m", max_file="2")

            # 运行容器
            container = client.containers.run(
                image=image_name,
                name=container_name,
                detach=True,
                environment=environment,
                restart_policy=restart_policy,
                log_config=log_config
            )
        except Exception as e:
            logger.error(f"部署容器{id}失败")
            logger.error(e)
        else:
            logger.info(f"部署容器{id}成功")

    def remove_docker(self, id):
        try:
            container = client.containers.get(f"{prefix}{id}")
            container.remove(force=True)
        except Exception as e:
            logger.error(f"删除容器{id}失败")
            logger.error(e)
        else:
            logger.info(f"删除容器{id}成功")

    def get_local_list(self):
        filters = {
            'name': f'{prefix}*'
        }
        containers = client.containers.list(all=True, filters=filters)
        local_list = []
        for container in containers:
            local_list.append(int(container.name.replace(prefix, "")))
        logger.info(f"本地存在{len(local_list)}个容器")
        return local_list

    def restart_docker(self, id):
        try:
            if int(id) not in self.local_list:
                return self.sync()
            else:
                container = client.containers.get(f"{prefix}{id}")
                container.restart(timeout=0)
        except Exception as e:
            logger.error(f"重启容器{id}失败")
            logger.error(e)
        else:
            logger.info(f"重启容器{id}成功")

    def get_remote_list(self):
        result_list = self.api.get_task_list()
        if result_list is None or result_list is False:
            logger.info("获取云端任务列表失败，使用本地列表")
            return self.local_list
        else:
            logger.info(f"从云端获取到{len(result_list)}个任务")
            return result_list

    def sync(self):
        logger.info("开始同步")
        self.local_list = self.get_local_list()
        remote_list = self.get_remote_list()
        local_set = set(self.local_list)
        remote_set = set(remote_list)

        for id in local_set - remote_set:
            self.remove_docker(id)
            self.local_list.remove(id)

        for id in remote_set - local_set:
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
        current_image_id = client.images.get(f'{image_name}').id
        try:
            client.images.pull(image_name)
            update_image_id = client.images.get(f'{image_name}').id
        except BaseException:
            print(f'远程不存在镜像 {image_name}')
            exit()
        if current_image_id != update_image_id:
            logger.info("检测到镜像更新")
            remove_local_docker()
            self.sync()
            logger.info("更新完成")
        else:
            logger.info("无需更新")


def job():
    global Local
    logger.info("开始定时任务")
    Local.sync()


def update():
    global Local
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

    @app.route('/syncTask', methods=['POST'])
    def resync():
        logging.info("收到同步任务请求")
        thread_add_task = threading.Thread(target=Local.sync)
        thread_add_task.start()
        data = {'status': True, 'msg': '同步成功'}
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


def remove_local_docker():
    containers = client.containers.list(all=True, filters={"name": f"{prefix}*"})
    for container in containers:
        container.remove(force=True)


def main():
    logger.info("AppleAuto后端管理服务启动")
    api = API()
    backend_api_result = api.get_backend_api()
    global Local
    Local = local_docker(api)
    logger.info("拉取最新镜像")
    client.images.pull(image_name)
    logger.info("删除本地所有容器")
    remove_local_docker()

    if backend_api_result is not None and backend_api_result['enable']:
        thread_app = threading.Thread(target=start_app, daemon=True, args=(
            backend_api_result['listen_ip'], backend_api_result['listen_port'], backend_api_result['token']))
        thread_app.start()
    job()
    logger.info(f"同步间隔为{sync_time}分钟")
    schedule.every(sync_time).minutes.do(job)
    if enable_auto_update:
        logger.info("启用自动更新")
        schedule.every(8).hours.do(update)
    while True:
        schedule.run_pending()
        time.sleep(1)


if __name__ == '__main__':
    main()

import os

prefix = "apple-auto_"


def get_local_list():
    local_list = []
    result = os.popen("docker ps --format \"{{.Names}}\" -a")
    for line in result.readlines():
        if line.find(prefix) != -1:
            local_list.append(line.strip().split("_")[1])
    print(f"本地存在{len(local_list)}个容器")
    return local_list


def remove_docker():
    local_list = get_local_list()
    for name in local_list:
        os.system(f"docker stop {prefix}{name} && docker rm {prefix}{name}")
        print(f"删除容器{name}")


if __name__ == '__main__':
    print("appleauto更新脚本")
    print(f"当前默认容器前缀为{prefix}，如需修改请修改脚本")
    print("请按回车键继续")
    input()
    print("开始更新appleauto")
    print("停止appleauto服务……")
    os.system("systemctl stop appleauto")
    print("删除appleauto容器……")
    remove_docker()
    print("删除docker镜像……")
    os.system("docker rmi sahuidhsu/appleid_auto")
    print("更新完成，开始重启appleauto服务……")
    os.system("systemctl start appleauto")
    print("重启完成，更新完成")

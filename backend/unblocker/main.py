import argparse
import datetime
import logging
import os
import random
import re
import string
import time
from json import loads

import ddddocr
import schedule
from requests import get
from selenium import webdriver
from telegram.ext import Updater, CommandHandler

parser = argparse.ArgumentParser(description="")
parser.add_argument("--config_path", help="配置文件路径")
parser.add_argument("--username", help="Apple ID 用户名")
parser.add_argument("--password", help="Apple ID 密码")
parser.add_argument("--dob", help="Apple ID 生日")
parser.add_argument("--question1", help="密保问题1")
parser.add_argument("--answer1", help="密保答案1")
parser.add_argument("--question2", help="密保问题2")
parser.add_argument("--answer2", help="密保答案2")
parser.add_argument("--question3", help="密保问题3")
parser.add_argument("--answer3", help="密保答案3")
parser.add_argument("--check_interval", help="检测间隔")
parser.add_argument("--api_url", help="API URL")
parser.add_argument("--api_key", help="API key")
parser.add_argument("--tgbot_chatid", help="Telegram Bot Chat ID")
parser.add_argument("--tgbot_token", help="Telegram Bot Token")
args = parser.parse_args()


class Config:
    def __init__(self):
        self.remote_driver = False
        self.tgbot_enable = False
        self.api_enable = False
        self.password_length = 10
        if args.config_path != "" or os.path.exists("config.example.json"):  # 读取配置文件
            configfile = open("config.example.json" if args.config_path == "" else args.config_path, "r",
                              encoding='utf-8')
            self.configdata = loads(configfile.read())
            configfile.close()
            self.username = self.configdata["id_username"]
            self.password = self.configdata["id_password"]
            self.dob = self.configdata["id_dob"]
            self.answer = self.configdata["answer"]
            self.webdriver = self.configdata["webdriver"]
            self.step_sleep = self.configdata["step_sleep"]
            self.check_interval = self.configdata["check_interval"]
            if self.configdata["api_url"] != "" and self.configdata["api_key"] != "":
                self.api_enable = True
                self.api_url = self.configdata["api_url"]
                self.api_key = self.configdata["api_key"]
            if self.webdriver != "local":
                self.remote_driver = True
            if self.configdata["tgbot_chatid"] != "" and self.configdata["tgbot_token"] != "":
                self.tgbot_enable = True
                self.tgbot_chatid = self.configdata["tgbot_chatid"]
                self.tgbot_token = self.configdata["tgbot_token"]
        else:  # 读取命令行参数
            self.username = args.username
            self.password = args.password
            self.dob = args.dob
            self.answer = {args.answer1: args.question1, args.answer2: args.question2, args.answer3: args.question3}
            self.check_interval = args.check_interval
            if args.api_url != "" and args.api_key != "":
                self.api_enable = True
                self.api_url = args.api_url
                self.api_key = args.api_key
            if args.tgbot_chatid != "" and args.tgbot_token != "":
                self.tgbot_enable = True
                self.tgbot_chatid = args.tgbot_chatid
                self.tgbot_token = args.tgbot_token
            if self.webdriver != "local":
                self.remote_driver = True
            if self.username == "" or self.password == "":
                print("用户名或密码为空")
                exit()
            if self.webdriver == "":
                print("webdriver为空")
                exit()


config = Config()
ocr = ddddocr.DdddOcr()


class TGbot:
    def __init__(self, chatid, token):
        self.updater = Updater(token)
        self.updater.dispatcher.add_handler(CommandHandler('ping', self.ping))
        self.updater.dispatcher.add_handler(CommandHandler('job', self.job))
        self.updater.start_polling()

    def ping(self, bot, update):
        info("Telegram 检测存活")
        self.sendmessage("还活着捏")

    def job(self, bot, update):
        info("手动执行任务")
        self.sendmessage("开始检测账号")
        job()

    def sendmessage(self, text):
        return self.updater.bot.send_message(chat_id=config.tgbot_chatid, text=text)["message_id"]


if config.tgbot_enable:
    tgbot = TGbot(config.tgbot_chatid, config.tgbot_token)


def notification(content):
    if config.tgbot_enable:
        tgbot.sendmessage(content)


class API:
    def __init__(self, url, key):
        self.url = url
        self.key = key

    def update(self, username, password):
        get(f"{self.url}?key={self.key}&username={username}password={password}")


if config.api_enable:
    api = API(config.api_url, config.api_key)


class ID:
    def __init__(self, username, password, dob, answer):
        self.username = username
        self.password = password
        self.dob = dob
        self.answer = answer

    def generate_password(self):
        pw = ""
        str = string.digits * 2 + string.ascii_letters
        while not (re.match(r'^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)', pw)):
            pw = ''.join(random.sample(str, k=config.password_length))
        return pw

    def get_answer(self, question):
        for item in self.answer:
            if question.find(item) != -1:
                return self.answer.get(item)

    def refresh(self):
        driver.get("https://iforgot.apple.com/password/verify/appleid?language=en_US")
        try:
            driver.switch_to.alert.accept()
        except BaseException:
            pass
        time.sleep(config.step_sleep)

    def login(self):
        self.refresh()
        time.sleep(config.step_sleep)
        driver.find_element("xpath",
                            "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/global-v2/div/idms-flow/div/forgot-password/div/div/div[1]/idms-step/div/div/div/div[2]/div/div[1]/div[1]/div/idms-textbox/idms-error-wrapper/div/div/input").send_keys(
            self.username)
        img = driver.find_element("xpath",
                                  "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/global-v2/div/idms-flow/div/forgot-password/div/div/div[1]/idms-step/div/div/div/div[2]/div/div[1]/div[2]/div/iforgot-captcha/div/div[1]/idms-captcha/div/div/img").get_attribute(
            "src")
        img = img.replace('data:image/jpeg;base64, ', '')
        code = ocr.classification(img)
        driver.find_element("xpath",
                            "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/global-v2/div/idms-flow/div/forgot-password/div/div/div[1]/idms-step/div/div/div/div[2]/div/div[1]/div[2]/div/iforgot-captcha/div/div[2]/idms-textbox/idms-error-wrapper/div/div/input").send_keys(
            code)
        time.sleep(config.step_sleep)
        driver.find_element("xpath",
                            "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/global-v2/div/idms-flow/div/forgot-password/div/div/div[1]/idms-step/div/div/div/div[3]/idms-toolbar/div/div/div/button").click()
        time.sleep(5)
        try:
            driver.find_element("xpath",
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/global-v2/div/idms-flow/div/forgot-password/div/div/div[1]/idms-step/div/div/div/div[2]/div/div[1]/div[2]/div/iforgot-captcha/div/div[2]/idms-textbox/idms-error-wrapper/div/idms-error/div/div/span")
        except BaseException:
            info("登录成功")
            return True
        else:
            info("验证码错误，重新登录")
            return self.login()

    def check(self):
        time.sleep(config.step_sleep)

        try:
            driver.find_element("xpath",
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/authentication-method/div[1]/p[1]").get_attribute(
                "innerHTML")
        except BaseException:
            info("当前账号未被锁定")
            return True  # 未被锁定
        else:
            info("当前账号已被锁定")
            return False  # 被锁定

    def check_2fa(self):
        try:
            driver.find_element("xpath",
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/hsa-two-v2/recovery-web-app/idms-flow/div/div/trusted-phone-number/div/h1")
        except BaseException:
            info("当前账号未开启2FA")
            return False  # 未开启2FA
        else:
            info("当前账号已开启2FA")
            return True  # 已开启2FA

    def unlock_2fa(self):
        if self.check_2fa():
            driver.find_element("xpath",
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/hsa-two-v2/recovery-web-app/idms-flow/div/div/trusted-phone-number/div/div/div[1]/idms-step/div/div/div/div[2]/div/div/div/button").click()
            time.sleep(config.step_sleep)
            driver.find_element("xpath",
                                "/html/body/div[5]/div/div/recovery-unenroll-start/div/idms-step/div/div/div/div[3]/idms-toolbar/div/div/div/button[1]").click()
            time.sleep(config.step_sleep)
            driver.find_element("xpath",
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/hsa-two-v2/recovery-web-app/idms-flow/div/div/verify-birthday/div/div/div[1]/idms-step/div/div/div/div[2]/div/form-fragment-birthday/masked-date/div/idms-error-wrapper/div/div/input").send_keys(
                self.dob)
            time.sleep(config.step_sleep)
            driver.find_element("xpath",
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/hsa-two-v2/recovery-web-app/idms-flow/div/div/verify-birthday/div/div/div[1]/idms-step/div/div/div/div[3]/idms-toolbar/div/div/div/button[1]").click()
            time.sleep(config.step_sleep)
            question1 = driver.find_element("xpath",
                                            "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/hsa-two-v2/recovery-web-app/idms-flow/div/div/verify-security-questions/div/div/div/step-challenge-security-questions/idms-step/div/div/div/div[2]/div/div[1]/div/label").get_attribute(
                "innerHTML")
            question2 = driver.find_element("xpath",
                                            "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/hsa-two-v2/recovery-web-app/idms-flow/div/div/verify-security-questions/div/div/div/step-challenge-security-questions/idms-step/div/div/div/div[2]/div/div[2]/div/label").get_attribute(
                "innerHTML")
            driver.find_element("xpath",
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/hsa-two-v2/recovery-web-app/idms-flow/div/div/verify-security-questions/div/div/div/step-challenge-security-questions/idms-step/div/div/div/div[2]/div/div[1]/div/div/idms-textbox/idms-error-wrapper/div/div/input").send_keys(
                self.get_answer(question1))
            driver.find_element("xpath",
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/hsa-two-v2/recovery-web-app/idms-flow/div/div/verify-security-questions/div/div/div/step-challenge-security-questions/idms-step/div/div/div/div[2]/div/div[2]/div/div/idms-textbox/idms-error-wrapper/div/div/input").send_keys(
                self.get_answer(question2))
            driver.find_element("xpath",
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/hsa-two-v2/recovery-web-app/idms-flow/div/div/verify-security-questions/div/div/div/step-challenge-security-questions/idms-step/div/div/div/div[3]/idms-toolbar/div/div/div/button[1]").click()
            time.sleep(5)
            driver.find_element("xpath",
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/hsa-two-v2/recovery-web-app/idms-flow/div/div/recovery-unenroll-prompt/div/div/div/div/idms-step/div/div/div/div[3]/idms-toolbar/div/div/div/button[1]").click()
            time.sleep(config.step_sleep)
            self.password = self.generate_password()
            driver.find_element("xpath",
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/hsa-two-v2/recovery-web-app/idms-flow/div/div/reset-password/div/div/div/div[1]/idms-password/idms-step/div/div/div/div[2]/div/div[1]/div/div[1]/div/new-password/div/idms-textbox/idms-error-wrapper/div/div/input").send_keys(
                self.password)
            driver.find_element("xpath",
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/hsa-two-v2/recovery-web-app/idms-flow/div/div/reset-password/div/div/div/div[1]/idms-password/idms-step/div/div/div/div[2]/div/div[1]/div/div[2]/div/confirm-password-input/div/idms-textbox/idms-error-wrapper/div/div/input").send_keys(
                self.password)
            time.sleep(config.step_sleep)
            driver.find_element("xpath",
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/hsa-two-v2/recovery-web-app/idms-flow/div/div/reset-password/div/div/div/div[1]/idms-password/idms-step/div/div/div/div[3]/idms-toolbar/div/div/div/button[1]").click()
            time.sleep(config.step_sleep)
            driver.find_element("xpath",
                                "/html/body/div[5]/div/div/div[1]/idms-step/div/div/div/div[3]/idms-toolbar/div/div/div/button[1]").click()
            info(f"新密码：{self.password}")
            time.sleep(10)

    def unlock(self):
        if not (self.check()):
            # 选择选项
            driver.find_element("xpath",
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/authentication-method/div[2]/div[2]/label/span").click()
            time.sleep(config.step_sleep)
            driver.find_element("id", "action").click()
            # 填写生日
            time.sleep(config.step_sleep)
            driver.find_element("xpath",
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/birthday/div[2]/div/masked-date/div/idms-error-wrapper/div/div/input").send_keys(
                self.dob)
            time.sleep(config.step_sleep)
            driver.find_element("id", "action").click()
            time.sleep(config.step_sleep)
            # 判断问题
            question1 = driver.find_element("xpath",
                                            "//*[@id='content']/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/verify-security-questions/div[2]/div[1]/label").get_attribute(
                "innerHTML")
            question2 = driver.find_element("xpath",
                                            "//*[@id='content']/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/verify-security-questions/div[2]/div[2]/label").get_attribute(
                "innerHTML")
            driver.find_element("xpath",
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/verify-security-questions/div[2]/div[1]/idms-textbox/idms-error-wrapper/div/div/input").send_keys(
                self.get_answer(question1))
            driver.find_element("xpath",
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/verify-security-questions/div[2]/div[2]/idms-textbox/idms-error-wrapper/div/div/input").send_keys(
                self.get_answer(question2))
            driver.find_element("id", "action").click()
            time.sleep(config.step_sleep)
            driver.find_element("xpath",
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/web-reset-options/div[2]/div[1]/button").click()
            time.sleep(config.step_sleep)
            self.password = self.generate_password()
            info(f"新密码：{self.password}")
            driver.find_element("xpath",
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/reset-password/div[2]/div[1]/div[1]/div/web-password-input/div/input").send_keys(
                self.password)
            driver.find_element("xpath",
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/reset-password/div[2]/div[1]/div[2]/div/confirm-password-input/div/idms-textbox/idms-error-wrapper/div/div/input").send_keys(
                self.password)
            driver.find_element("id", "action").click()
            time.sleep(10)


def info(text):
    logging.info(text)
    print(datetime.datetime.now().strftime("%H:%M:%S"), "[INFO]", text)


def error(text):
    logging.critical(text)
    print(datetime.datetime.now().strftime("%H:%M:%S"), "[ERROR]", text)


def setup_driver():
    global driver
    options = webdriver.ChromeOptions()
    options.add_argument("--no-sandbox")
    options.add_argument("--headless")
    options.add_argument("--disable-gpu")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--ignore-certificate-errors")
    options.add_argument("enable-automation")
    options.add_argument("--disable-extensions")
    options.add_argument("start-maximized")
    options.add_argument("window-size=1920,1080")
    options.add_argument("user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) "
                         "Chrome/101.0.4951.54 Safari/537.36")
    try:
        if config.remote_driver:
            driver = webdriver.Remote(command_executor=config.webdriver, options=options)
        else:
            driver = webdriver.Chrome(options=options)
    except BaseException as e:
        print(e)
        error("Webdriver调用失败")
    else:
        driver.set_page_load_timeout(15)


def job():
    schedule.clear()
    unlock = False
    setup_driver()
    id.login()
    if id.check_2fa():
        info("检测到账号开启双重认证，开始解锁")
        id.unlock_2fa()
        unlock = True
    else:
        if not (id.check()):
            info("检测到账号被锁定，开始解锁")
            id.unlock()
            unlock = True
    info("账号检测完毕")
    driver.quit()
    if config.api_enable:
        api.update(id.username, id.password)
    if unlock:
        notification(f"Apple ID解锁成功\n新密码：{id.password}")
    schedule.every(config.check_interval).minutes.do(job)
    return unlock


def main():
    global id
    id = ID(config.username, config.password, config.dob, config.answer)
    job()
    while True:
        schedule.run_pending()
        time.sleep(1)


if __name__ == '__main__':
    main()

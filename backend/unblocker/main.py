import argparse
import datetime
import logging
import random
import re
import string
import time
from json import loads

import ddddocr
import schedule
from requests import get, post
from selenium import webdriver

parser = argparse.ArgumentParser(description="")
parser.add_argument("-api_url", help="API URL")
parser.add_argument("-api_key", help="API key")
parser.add_argument("-taskid", help="Task ID")
args = parser.parse_args()


class API:
    def __init__(self, url, key):
        self.url = url
        self.key = key

    def get_config(self, id):
        try:
            result = loads(get(f"{self.url}/api/?key={self.key}&action=get_task_info&id={id}", verify=False).text)
        except BaseException:
            return {"status": "fail"}
        else:
            if result["status"] == "success":
                return result
            else:
                return {"status": "fail"}

    def update(self, username, password):
        try:
            result = loads(
                get(f"{self.url}/api/?key={self.key}&username={username}&password={password}&action=update_password",
                    verify=False).text)
        except BaseException:
            return {"status": "fail"}
        else:
            if result["status"] == "success":
                return result
            else:
                return {"status": "fail"}


class Config:
    def __init__(self, username, dob, q1, a1, q2, a2, q3, a3, check_interval, tgbot_token, tgbot_chatid, step_sleep,
                 webdriver):
        self.tgbot_enable = False
        self.password_length = 10
        self.username = username
        self.dob = dob
        self.answer = {q1: a1, q2: a2, q3: a3}
        self.check_interval = check_interval
        self.webdriver = webdriver
        self.step_sleep = step_sleep
        if tgbot_chatid != "" and tgbot_token != "":
            self.tgbot_enable = True
            self.tgbot_chatid = tgbot_chatid
            self.tgbot_token = tgbot_token

    def __str__(self) -> str:
        return f"Username: {self.username}\n" \
               f"DOB: {self.dob}\n" \
               f"Answer: {self.answer}\n" \
               f"Check Interval: {self.check_interval}\n" \
               f"Webdriver: {self.webdriver}\n" \
               f"Step Sleep: {self.step_sleep}\n" \
               f"Telegram Bot: {self.tgbot_enable}\n" \
               f"Password Length: {self.password_length}"


class ID:
    def __init__(self, username, dob, answer):
        self.username = username
        self.password = ""
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
        try:
            text = driver.find_element("xpath", "/html/body/center[1]/h1").text
        except BaseException:
            time.sleep(config.step_sleep)
            return True
        else:
            error("页面加载失败，疑似服务器IP被拒绝访问，程序已退出")
            error(text)
            driver.quit()
            return False

    def login(self):
        if not (self.refresh()):
            return False
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
            try:
                message = driver.find_element("xpath",
                                              "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/global-v2/div/idms-flow/div/forgot-password/div/div/div[1]/idms-step/div/div/div/div[2]/div/div[1]/div[1]/div/idms-textbox/idms-error-wrapper/div/idms-error/div/div/span")
            except BaseException:
                pass
            else:
                error("无法处理请求，可能是账号已失效，程序已退出")
                error(message.text)
                driver.quit()
                return False
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
            try:
                driver.find_element("xpath",
                                    "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/hsa-two-v2/recovery-web-app/idms-flow/div/div/trusted-phone-number/div/div/div[1]/idms-step/div/div/div/div[2]/div/div/div/button").click()
            except BaseException:
                error("无法找到关闭验证按钮，可能是账号不允许关闭2FA，退出程序")
                driver.quit()
                exit()
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
            try:
                question1 = driver.find_element("xpath",
                                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/hsa-two-v2/recovery-web-app/idms-flow/div/div/verify-security-questions/div/div/div/step-challenge-security-questions/idms-step/div/div/div/div[2]/div/div[1]/div/label").get_attribute(
                    "innerHTML")
            except BaseException:
                error("无法找到问题，可能是账号不允许关闭2FA，退出程序")
                driver.quit()
                exit()
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
            try:
                question1 = driver.find_element("xpath",
                                                "//*[@id='content']/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/verify-security-questions/div[2]/div[1]/label").get_attribute(
                    "innerHTML")
            except BaseException:
                error("安全问题获取失败，可能是上一步生日填写错误")
                driver.quit()
                exit()
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
            try:
                driver.find_element("xpath",
                                    "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/web-reset-options/div[2]/div[1]/button").click()
            except BaseException:
                error("无法重置密码，可能是上一步问题回答错误")
                driver.quit()
                exit()
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


api = API(args.api_url, args.api_key)
config_result = api.get_config(args.taskid)
if config_result["status"] == "fail":
    error("从API获取配置失败")
    exit()
config = Config(config_result["username"], config_result["dob"], config_result["q1"], config_result["a1"],
                config_result["q2"], config_result["a2"], config_result["q3"], config_result["a3"],
                config_result["check_interval"], config_result["tgbot_token"], config_result["tgbot_chatid"],
                config_result["step_sleep"], config_result["webdriver"])


def notification(content):
    if config.tgbot_enable:
        post(f"https://api.telegram.org/bot{config.tgbot_token}/sendMessage",
             data={"chat_id": config.tgbot_chatid, "text": content})


ocr = ddddocr.DdddOcr()


def setup_driver():
    global driver
    options = webdriver.ChromeOptions()
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-gpu")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--ignore-certificate-errors")
    options.add_argument("enable-automation")
    options.add_argument("--disable-extensions")
    options.add_argument("start-maximized")
    options.add_argument("window-size=1920,1080")
    options.add_argument("user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) "
                         "Chrome/107.0.0.0 Safari/537.36")
    try:
        if config.webdriver != "local":
            driver = webdriver.Remote(command_executor=config.webdriver, options=options)
        else:
            driver = webdriver.Chrome(options=options)
    except BaseException as e:
        error("Webdriver调用失败")
        print(e)
        exit()
    else:
        driver.set_page_load_timeout(15)


def job():
    global api
    schedule.clear()
    unlock = False
    setup_driver()
    if id.login():
        if id.check_2fa():
            info("检测到账号开启双重认证，开始解锁")
            id.unlock_2fa()
            unlock = True
        else:
            if not (id.check()):
                info("检测到账号被锁定，开始解锁")
                id.unlock()
                unlock = True
        driver.quit()
        info("账号检测完毕")
        if unlock:
            notification(f"Apple ID解锁成功\n新密码：{id.password}")
            update_result = api.update(id.username, id.password)
        else:
            update_result = api.update(id.username, "")
        if update_result["status"] == "fail":
            error("更新密码失败")
        else:
            info("更新密码成功")
    else:
        error("任务执行失败，等待下次检测")
    schedule.every(config.check_interval).minutes.do(job)
    return unlock


id = ID(config.username, config.dob, config.answer)
job()
while True:
    schedule.run_pending()
    time.sleep(1)

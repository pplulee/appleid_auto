import argparse
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
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.wait import WebDriverWait

parser = argparse.ArgumentParser(description="")
parser.add_argument("-api_url", help="API URL")
parser.add_argument("-api_key", help="API key")
parser.add_argument("-taskid", help="Task ID")
args = parser.parse_args()

logger = logging.getLogger()
logger.setLevel('INFO')
BASIC_FORMAT = "%(asctime)s [%(levelname)s] %(message)s"
DATE_FORMAT = "%Y-%m-%d %H:%M:%S"
formatter = logging.Formatter(BASIC_FORMAT, DATE_FORMAT)
chlr = logging.StreamHandler()
chlr.setFormatter(formatter)
logger.addHandler(chlr)


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

    def get_password(self, username):
        try:
            result = loads(
                get(f"{self.url}/api/?key={self.key}&username={username}&action=get_password",
                    verify=False).text)
        except BaseException:
            return ""
        else:
            if result["status"] == "success":
                return result["password"]
            else:
                return ""


class Config:
    def __init__(self, username, password, dob, q1, a1, q2, a2, q3, a3, check_interval, tgbot_token, tgbot_chatid,
                 step_sleep,
                 webdriver, proxy):
        self.tgbot_enable = False
        self.password_length = 10
        self.username = username
        self.password = password
        self.dob = dob
        self.answer = {q1: a1, q2: a2, q3: a3}
        self.check_interval = check_interval
        self.webdriver = webdriver
        self.step_sleep = step_sleep
        self.proxy = proxy
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
        return ""

    def refresh(self):
        try:
            driver.get("https://iforgot.apple.com/password/verify/appleid?language=en_US")
            WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CLASS_NAME, "app-title")))
        except BaseException:
            logger.error("刷新页面失败")
            logger.error("若启用了代理，请检查代理是否可用")
            driver.quit()
            return False
        try:
            driver.switch_to.alert.accept()
        except BaseException:
            pass
        try:
            text = driver.find_element(By.XPATH, "/html/body/center[1]/h1").text
        except BaseException:
            return True
        else:
            logger.error("页面加载失败，疑似服务器IP被拒绝访问")
            logger.error(text)
            driver.quit()
            return False

    def login(self):
        if not (self.refresh()):
            return False
        time.sleep(config.step_sleep)
        try:
            driver.find_element(By.XPATH,
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/global-v2/div/idms-flow/div/forgot-password/div/div/div[1]/idms-step/div/div/div/div[2]/div/div[1]/div[1]/div/idms-textbox/idms-error-wrapper/div/div/input").send_keys(
                self.username)
        except BaseException:
            logger.error("无法获取页面内容，即将退出程序")
            logger.error("若启用了代理，请检查代理是否可用")
            driver.quit()
            exit()
        img = driver.find_element(By.XPATH,
                                  "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/global-v2/div/idms-flow/div/forgot-password/div/div/div[1]/idms-step/div/div/div/div[2]/div/div[1]/div[2]/div/iforgot-captcha/div/div[1]/idms-captcha/div/div/img").get_attribute(
            "src")
        img = img.replace('data:image/jpeg;base64, ', '')
        code = ocr.classification(img)
        driver.find_element(By.XPATH,
                            "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/global-v2/div/idms-flow/div/forgot-password/div/div/div[1]/idms-step/div/div/div/div[2]/div/div[1]/div[2]/div/iforgot-captcha/div/div[2]/idms-textbox/idms-error-wrapper/div/div/input").send_keys(
            code)
        time.sleep(config.step_sleep)
        driver.find_element(By.XPATH,
                            "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/global-v2/div/idms-flow/div/forgot-password/div/div/div[1]/idms-step/div/div/div/div[3]/idms-toolbar/div/div/div/button").click()
        time.sleep(5)
        try:
            driver.find_element(By.XPATH,
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/global-v2/div/idms-flow/div/forgot-password/div/div/div[1]/idms-step/div/div/div/div[2]/div/div[1]/div[2]/div/iforgot-captcha/div/div[2]/idms-textbox/idms-error-wrapper/div/idms-error/div/div/span")
        except BaseException:
            try:
                message = driver.find_element(By.XPATH,
                                              "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/global-v2/div/idms-flow/div/forgot-password/div/div/div[1]/idms-step/div/div/div/div[2]/div/div[1]/div[1]/div/idms-textbox/idms-error-wrapper/div/idms-error/div/div/span")
            except BaseException:
                pass
            else:
                logger.error("无法处理请求，可能是服务器IP被苹果拉黑")
                logger.error(message.text)
                return False
            logger.info("登录成功")
            return True
        else:
            logger.info("验证码错误，重新登录")
            return self.login()

    def check(self):
        time.sleep(config.step_sleep)
        try:
            driver.find_element(By.XPATH,
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/authentication-method/div[1]/p[1]").get_attribute(
                "innerHTML")
        except BaseException:
            logger.info("当前账号未被锁定")
            return True  # 未被锁定
        else:
            logger.info("当前账号已被锁定")
            return False  # 被锁定

    def check_2fa(self):
        time.sleep(config.step_sleep)
        try:
            driver.find_element(By.XPATH,
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/hsa-two-v2/recovery-web-app/idms-flow/div/div/trusted-phone-number/div/h1")
        except BaseException:
            logger.info("当前账号未开启2FA")
            return False  # 未开启2FA
        else:
            logger.info("当前账号已开启2FA")
            return True  # 已开启2FA

    def unlock_2fa(self):
        if self.check_2fa():
            try:
                driver.find_element(By.XPATH,
                                    "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/hsa-two-v2/recovery-web-app/idms-flow/div/div/trusted-phone-number/div/div/div[1]/idms-step/div/div/div/div[2]/div/div/div/button").click()
            except BaseException:
                logger.error("无法找到关闭验证按钮，可能是账号不允许关闭2FA，退出程序")
                driver.quit()
                exit()
            WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.XPATH,
                                                                            "/html/body/div[5]/div/div/recovery-unenroll-start/div/idms-step/div/div/div/div[3]/idms-toolbar/div/div/div/button[1]"))).click()
            time.sleep(1)
            driver.find_element(By.CLASS_NAME, "generic-input-field").send_keys(self.dob)
            time.sleep(1)
            WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CLASS_NAME, "button-primary"))).click()
            try:
                WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CLASS_NAME, "question")))
                question_element = driver.find_elements(By.CLASS_NAME, "question")
            except BaseException:
                logger.error("安全问题获取失败，可能是生日错误，程序已退出")
                driver.quit()
                exit()
            answer_inputs = driver.find_elements(By.CLASS_NAME, "generic-input-field")
            answer_inputs[0].send_keys(self.get_answer(question_element[0].get_attribute("innerHTML")))
            time.sleep(1)
            answer_inputs[1].send_keys(self.get_answer(question_element[1].get_attribute("innerHTML")))
            WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CLASS_NAME, "button-primary"))).click()
            time.sleep(5)

            try:
                msg = driver.find_element(By.CLASS_NAME, "form-message").get_attribute("innerHTML").strip()
            except BaseException:
                pass
            else:
                logger.error(f"安全问题错误，程序已退出\n错误信息：{msg}")
                driver.quit()
                exit()
            WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CLASS_NAME, "pull-right"))).click()
            self.password = self.generate_password()
            WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.XPATH,
                                                                            "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/hsa-two-v2/recovery-web-app/idms-flow/div/div/reset-password/div/div/div/div[1]/idms-password/idms-step/div/div/div/div[2]/div/div[1]/div/div[1]/div/new-password/div/idms-textbox/idms-error-wrapper/div/div/input"))).send_keys(
                self.password)
            driver.find_element(By.XPATH,
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/hsa-two-v2/recovery-web-app/idms-flow/div/div/reset-password/div/div/div/div[1]/idms-password/idms-step/div/div/div/div[2]/div/div[1]/div/div[2]/div/confirm-password-input/div/idms-textbox/idms-error-wrapper/div/div/input").send_keys(
                self.password)
            time.sleep(1)
            driver.find_element(By.CLASS_NAME, "pull-right").click()
            WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.XPATH,
                                                                            "/html/body/div[5]/div/div/div[1]/idms-step/div/div/div/div[3]/idms-toolbar/div/div/div/button[1]"))).click()
            logger.info(f"新密码：{self.password}")
            WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.XPATH,
                                                                            "/html/body/div[5]/div/div/div[1]/idms-step/div/div/div/div[3]/idms-toolbar/div/div/div/button[1]")))

    def unlock(self):
        if not (self.check()):
            # 选择选项
            try:
                driver.find_element(By.XPATH,
                                    "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/authentication-method/div[2]/div[2]/label/span").click()
            except BaseException:
                logger.error("选择选项失败，无法使用安全问题解锁，程序已退出")
                driver.quit()
                exit()
            time.sleep(config.step_sleep)
            driver.find_element(By.ID, "action").click()
            # 填写生日
            time.sleep(config.step_sleep)
            driver.find_element(By.XPATH,
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/birthday/div[2]/div/masked-date/div/idms-error-wrapper/div/div/input").send_keys(
                self.dob)
            time.sleep(config.step_sleep)
            driver.find_element(By.ID, "action").click()
            time.sleep(config.step_sleep)
            # 判断问题
            try:
                question1 = driver.find_element(By.XPATH,
                                                "//*[@id='content']/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/verify-security-questions/div[2]/div[1]/label").get_attribute(
                    "innerHTML")
                question2 = driver.find_element(By.XPATH,
                                                "//*[@id='content']/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/verify-security-questions/div[2]/div[2]/label").get_attribute(
                    "innerHTML")
            except BaseException:
                logger.error("安全问题获取失败，可能是生日错误，程序已退出")
                driver.quit()
                exit()
            answer1 = self.get_answer(question1)
            answer2 = self.get_answer(question2)
            if answer1 == "" or answer2 == "":
                logger.error("无法找到答案，可能是安全问题错误，程序已退出")
                driver.quit()
                exit()
            driver.find_element(By.XPATH,
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/verify-security-questions/div[2]/div[1]/idms-textbox/idms-error-wrapper/div/div/input").send_keys(
                answer1)
            driver.find_element(By.XPATH,
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/verify-security-questions/div[2]/div[2]/idms-textbox/idms-error-wrapper/div/div/input").send_keys(
                answer2)
            driver.find_element(By.ID, "action").click()
            time.sleep(config.step_sleep)
            try:
                driver.find_element(By.XPATH,
                                    "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/web-reset-options/div[2]/div[1]/button").click()
            except BaseException:
                logger.error("无法重置密码，可能是上一步问题回答错误，程序已退出")
                driver.quit()
                exit()
            time.sleep(config.step_sleep)
            self.password = self.generate_password()
            logger.info(f"新密码：{self.password}")
            driver.find_element(By.XPATH,
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/reset-password/div[2]/div[1]/div[1]/div/web-password-input/div/input").send_keys(
                self.password)
            driver.find_element(By.XPATH,
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/reset-password/div[2]/div[1]/div[2]/div/confirm-password-input/div/idms-textbox/idms-error-wrapper/div/div/input").send_keys(
                self.password)
            driver.find_element(By.ID, "action").click()
            time.sleep(10)

    def delete_devices(self):
        logger.info("开始删除设备")
        driver.get("https://appleid.apple.com/sign-in")
        try:
            driver.switch_to.alert.accept()
        except BaseException:
            pass
        driver.switch_to.frame(WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.TAG_NAME, "iframe"))))
        WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.ID, "account_name_text_field"))).send_keys(
            self.username)
        time.sleep(1)
        driver.find_element(By.ID, "account_name_text_field").send_keys(Keys.ENTER)
        time.sleep(1)
        driver.find_element(By.ID, "password_text_field").send_keys(self.password)
        time.sleep(1)
        driver.find_element(By.ID, "account_name_text_field").send_keys(Keys.ENTER)
        time.sleep(config.step_sleep)
        try:
            msg = driver.find_element(By.ID, "errMsg").get_attribute("innerHTML")
        except BaseException:
            pass  # 没有错误信息
        else:
            logger.error(f"登陆失败，等待下次检测，错误信息：\n{msg}")
            return False
        question_element = driver.find_elements(By.CLASS_NAME, "question")
        answer_inputs = driver.find_elements(By.CLASS_NAME, "generic-input-field")
        answer_inputs[0].send_keys(self.get_answer(question_element[0].get_attribute("innerHTML")))
        answer_inputs[1].send_keys(self.get_answer(question_element[1].get_attribute("innerHTML")))
        driver.find_element(By.XPATH, "/html/body/div[4]/div/div/div[1]/div[3]/div/button[2]").click()
        time.sleep(config.step_sleep)
        try:
            driver.find_element(By.CLASS_NAME, "has-errors")
        except BaseException:
            pass
        else:
            logger.error("安全问题错误，程序已退出")
            driver.quit()
            exit()
        # 跳过双重验证
        driver.switch_to.frame(WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.TAG_NAME, "iframe"))))
        try:
            WebDriverWait(driver, 5).until(EC.presence_of_element_located((By.XPATH,
                                                                            "/html/body/div[1]/appleid-repair/idms-widget/div/div/div/hsa2-enrollment-flow/div/div/idms-step/div/div/div/div[3]/idms-toolbar/div/div[1]/div/button[2]"))).click()
            driver.find_element(By.CLASS_NAME, "nav-cancel").click()
            WebDriverWait(driver, 5).until_not(EC.presence_of_element_located((By.CLASS_NAME, "nav-cancel")))
        except BaseException:
            pass
        # 删除设备
        time.sleep(config.step_sleep)
        driver.get("https://appleid.apple.com/account/manage/section/devices")
        WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.XPATH, "//*[@id=\"root\"]/div[3]/main/div/div[2]/div[3]/div/div/header/h1")))
        time.sleep(config.step_sleep)
        devices = driver.find_elements(By.CLASS_NAME, "medium-12")
        logger.info(f"共有{len(devices)}个设备")
        for i in range(len(devices)):
            devices[i].click()
            WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CLASS_NAME, "button-secondary"))).click()
            WebDriverWait(driver, 10).until(EC.presence_of_element_located(
                (By.XPATH, "/html/body/aside[2]/div/div[2]/fieldset/div/div/button[2]"))).click()
            WebDriverWait(driver, 10).until_not(EC.presence_of_element_located((By.CLASS_NAME,"button-bar-working")))
            if i != len(devices) - 1:
                devices[i + 1].click()
        logger.info("设备删除完毕")
        return True


api = API(args.api_url, args.api_key)
config_result = api.get_config(args.taskid)
if config_result["status"] == "fail":
    logger.error("从API获取配置失败")
    exit()

config = Config(config_result["username"],
                config_result["password"] if "password" in config_result.keys() else api.get_password(
                    config_result["username"]),
                config_result["dob"],
                config_result["q1"],
                config_result["a1"],
                config_result["q2"],
                config_result["a2"],
                config_result["q3"],
                config_result["a3"],
                config_result["check_interval"],
                config_result["tgbot_token"] if "tgbot_token" in config_result.keys() else "",
                config_result["tgbot_chatid"] if "tgbot_chatid" in config_result.keys() else "",
                config_result["step_sleep"],
                config_result["webdriver"],
                config_result["proxy"] if "proxy" in config_result.keys() else "")


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
    if config.proxy != "":
        logger.info("已启用代理")
        options.add_argument(f"--proxy-server={config.proxy}")
    options.add_argument(
        "user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36")
    try:
        if config.webdriver != "local":
            driver = webdriver.Remote(command_executor=config.webdriver, options=options)
        else:
            driver = webdriver.Chrome(options=options)
    except BaseException as e:
        logger.error("Webdriver调用失败")
        logger.error(e)
        exit()
    else:
        driver.set_page_load_timeout(30)


def job():
    global api
    schedule.clear()
    unlock = False
    setup_driver()
    if id.login():
        if id.check_2fa():
            logger.info("检测到账号开启双重认证，开始解锁")
            id.unlock_2fa()
            unlock = True
        elif not (id.check()):
            logger.info("检测到账号被锁定，开始解锁")
            id.unlock()
            unlock = True
        logger.info("账号检测完毕")
        if unlock:
            notification(f"Apple ID解锁成功\n新密码：{id.password}")
            update_result = api.update(id.username, id.password)
        else:
            update_result = api.update(id.username, "")
        if update_result["status"] == "fail":
            logger.error("更新密码失败")
        else:
            logger.info("更新密码成功")
        id.delete_devices()
    else:
        logger.error("任务执行失败，等待下次检测")
    try:
        driver.quit()
    except BaseException:
        logger.error("Webdriver关闭失败")
    else:
        logger.info("关闭Webdriver窗口")
    schedule.every(config.check_interval).minutes.do(job)
    logger.info("已设置下次检测任务")
    return unlock


id = ID(config.username, config.password, config.dob, config.answer)
job()
while True:
    schedule.run_pending()
    time.sleep(1)

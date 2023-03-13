import argparse
import logging
import random
import re
import string
import time
from json import loads

import ddddocr
import schedule
import urllib3
from requests import get, post
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.wait import WebDriverWait

urllib3.disable_warnings()

parser = argparse.ArgumentParser(description="")
parser.add_argument("-api_url", help="API URL")
parser.add_argument("-api_key", help="API key")
parser.add_argument("-taskid", help="Task ID")
parser.add_argument("-lang.py", help="Output language")
args = parser.parse_args()

logger = logging.getLogger()
logger.setLevel('INFO')
BASIC_FORMAT = "%(asctime)s [%(levelname)s] %(message)s"
DATE_FORMAT = "%Y-%m-%d %H:%M:%S"
formatter = logging.Formatter(BASIC_FORMAT, DATE_FORMAT)
chlr = logging.StreamHandler()
chlr.setFormatter(formatter)
logger.addHandler(chlr)

lang_choose = args.lang if args.lang else "zh-cn"
from lang import zh_cn as lang
lang_text = lang()
if lang_choose == "en_us":
    from lang import en_us as lang
    lang_text = lang()

class API:
    def __init__(self, url, key):
        self.url = url
        self.key = key

    def get_config(self, id):
        try:
            result = loads(get(f"{self.url}/api/",
                               verify=False,
                               params={
                                   "key": self.key,
                                   "action": "get_task_info",
                                   "id": id
                               }).text)
        except BaseException as e:
            logger.error(lang_text.ErrorRetrievingConfig)
            logger.error(e)
            return {"status": "fail"}
        else:
            if result["status"] == "success":
                return result
            else:
                return {"status": "fail"}

    def update(self, username, password):
        try:
            result = loads(
                get(f"{self.url}/api/",
                    verify=False,
                    params={
                        "key": self.key,
                        "username": username,
                        "password": password,
                        "action": "update_password"
                    }).text)
        except BaseException as e:
            logger.error(lang_text.failOnPasswordUpdate)
            logger.error(e)
            return {"status": "fail"}
        else:
            if result["status"] == "success":
                return result
            else:
                return {"status": "fail"}

    def get_password(self, username):
        try:
            result = loads(
                get(f"{self.url}/api/",
                    verify=False,
                    params={
                        "key": self.key,
                        "username": username,
                        "action": "get_password"
                    }).text)
        except BaseException as e:
            logger.error(lang_text.failOnRetrievingPassword)
            logger.error(e)
            return ""
        else:
            if result["status"] == "success":
                return result["password"]
            else:
                return ""

    def update_message(self, username, message):
        try:
            result = loads(
                get(f"{self.url}/api/",
                    verify=False,
                    params={"key": self.key,
                            "username": username,
                            "message": message,
                            "action": "update_message"}).text)
        except BaseException as e:
            logger.error(lang_text.failOnMessageUpdate)
            logger.error(e)
            return False
        else:
            if result["status"] == "success":
                return True
            else:
                return False

    def report_proxy_error(self, proxy_id):
        try:
            result = loads(
                get(f"{self.url}/api/",
                    verify=False,
                    params={"key": self.key,
                            "id": proxy_id,
                            "action": "report_proxy_error"}).text)
        except BaseException as e:
            logger.error(lang_text.failOnReportingProxyError)
            logger.error(e)
            return False
        else:
            if result["status"] == "success":
                return True
            else:
                return False


class Config:
    def __init__(self, config_result):
        self.password_length = 10
        self.username = config_result["username"]
        self.password = config_result["password"] if "password" in config_result.keys() else api.get_password(
            self.username)
        self.dob = config_result["dob"]
        self.answer = {config_result["q1"]: config_result["a1"],
                       config_result["q2"]: config_result["a2"],
                       config_result["q3"]: config_result["a3"]}
        self.check_interval = config_result["check_interval"]
        self.webdriver = config_result["webdriver"]
        self.proxy = config_result["proxy"] if "proxy" in config_result.keys() else ""
        self.proxy_id = config_result["proxy_id"] if "proxy_id" in config_result.keys() else -1
        self.proxy_type = config_result["proxy_type"] if "proxy_type" in config_result.keys() else ""
        self.proxy_content = config_result["proxy_content"] if "proxy_content" in config_result.keys() else ""
        self.tgbot_chatid = config_result["tgbot_chatid"] if "tgbot_chatid" in config_result.keys() else ""
        self.tgbot_token = config_result["tgbot_token"] if "tgbot_token" in config_result.keys() else ""
        self.enable_check_password_correct = "check_password_correct" in config_result.keys()
        self.enable_delete_devices = "delete_devices" in config_result.keys()
        self.enable_auto_update_password = "auto_update_password" in config_result.keys()
        self.headless = "headless" in config_result.keys()
        if self.proxy_content!="" and self.proxy_type!="":
            # 新版本代理
            if self.proxy_type=="url":
                try:
                    self.proxy = get(self.proxy_content).text
                except BaseException as e:
                    logger.error(lang_text.failOnRetrievingProxyFromAPI)
                    logger.error(e)
                    self.proxy = ""
                else:
                    logger.info(f"{lang_text.retrievedProxyFromAPI}: {self.proxy}")
            elif self.proxy_type == "socks5" or self.proxy_type == "http":
                self.proxy = self.proxy_type+"://"+self.proxy_content
        if self.headless:
            logger.info(lang_text.backgroundRunning)
        if self.enable_delete_devices:
            logger.info(lang_text.removeDevice)
        if self.enable_check_password_correct:
            logger.info(lang_text.checkPassword)
        if self.enable_auto_update_password:
            logger.info(lang_text.autoUpdatePassword)
        if self.proxy_id != -1:
            logger.info(f"{lang_text.usingProxyID}: {self.proxy_id}")


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
            try:
                driver.switch_to.alert.accept()
            except BaseException:
                pass
            WebDriverWait(driver, 30 if config.proxy != "" else 10).until(
                EC.presence_of_element_located((By.CLASS_NAME, "iforgot-apple-id")))
        except BaseException:
            logger.error(lang_text.failOnRefreshingPage)
            if config.proxy != "":
                logger.error(lang_text.proxyEnabledRefreshing)
                api.update_message(self.username, lang_text.proxyEnabledRefreshingAPI)
                api.report_proxy_error(config.proxy_id)
                notification(lang_text.proxyEnabledRefreshingAPI)
            else:
                api.update_message(self.username, lang_text.failOnLoadingPage)
                notification(lang_text.failOnLoadingPage)
            get_ip()
            return False
        try:
            text = driver.find_element(By.XPATH, "/html/body/center[1]/h1").text
        except BaseException:
            return True
        else:
            logger.error(lang_text.IPBlocked)
            logger.error(text)
            api.update_message(self.username, lang_text.seeLog)
            if config.proxy != "":
                api.report_proxy_error(config.proxy_id)
            notification(lang_text.seeLog)
            get_ip()
            return False

    def process_verify(self):
        # 需要先调用login到达页面
        try:
            img = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.TAG_NAME, "img"))).get_attribute(
                "src").replace('data:image/jpeg;base64, ', '')
            code = ocr.classification(img)
            driver.find_element(By.CLASS_NAME, "captcha-input").send_keys(code)
        except BaseException:
            logger.error(lang_text.failOnGettingCaptcha)
            return False
        else:
            return True

    def login(self):
        if not (self.refresh()):
            return False
        try:
            WebDriverWait(driver, 7).until(
                EC.presence_of_element_located((By.CLASS_NAME, "iforgot-apple-id"))).send_keys(self.username)
        except BaseException:
            logger.error(lang_text.failOnRetrievingPage)
            if config.proxy != "":
                logger.error(lang_text.proxyEnabledRefreshing)
                api.update_message(self.username, lang_text.proxyEnabledGettingContent)
                api.report_proxy_error(config.proxy_id)
                notification(lang_text.proxyEnabledGettingContent)
            else:
                api.update_message(self.username, lang_text.failOnGettingPage)
                notification(lang_text.failOnGettingPage)
            return False
        while True:
            if not self.process_verify():
                return False
            time.sleep(1)
            WebDriverWait(driver, 5).until(EC.presence_of_element_located((By.CLASS_NAME, "button-primary"))).click()
            try:
                # 验证码错误
                WebDriverWait(driver, 3).until(EC.presence_of_element_located((By.XPATH,
                                                                               "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/global-v2/div/idms-flow/div/forgot-password/div/div/div[1]/idms-step/div/div/div/div[2]/div/div[1]/div[2]/div/iforgot-captcha/div/div[2]/idms-textbox/idms-error-wrapper/div/idms-error/div/div/span")))
            except BaseException:
                logger.info(lang_text.captchaCorrect)
                break
            else:
                logger.info(lang_text.captchaFail)
                continue

        try:
            msg = WebDriverWait(driver, 3).until(EC.presence_of_element_located((By.XPATH,
                                                                                 "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/global-v2/div/idms-flow/div/forgot-password/div/div/div[1]/idms-step/div/div/div/div[2]/div/div[1]/div[1]/div/idms-textbox/idms-error-wrapper/div/idms-error/div/div/span"))).get_attribute(
                "innerHTML")
        except BaseException:
            logger.info(lang_text.login)
            return True
        else:
            logger.error(f"{lang_text.blocked}\n{msg.strip()}")
            api.update_message(self.username, lang_text.loginFailCheckLog)
            notification(lang_text.loginFailCheckLog)
            get_ip()
            return False

    def check(self):
        try:
            driver.find_element(By.XPATH,
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/authentication-method/div[1]/p[1]")
        except BaseException:
            logger.info("当前账号未被锁定")
            return True  # 未被锁定
        else:
            logger.info("当前账号已被锁定")
            return False  # 被锁定

    def check_2fa(self):
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
                logger.error("无法找到关闭验证按钮，可能是账号不允许关闭2FA")
                api.update_message(self.username, "关闭二步验证失败，可能是账号不允许关闭2FA")
                notification("关闭二步验证失败，可能是账号不允许关闭2FA")
                return False
            WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.XPATH,
                                                                            "/html/body/div[5]/div/div/recovery-unenroll-start/div/idms-step/div/div/div/div[3]/idms-toolbar/div/div/div/button[1]"))).click()
            time.sleep(1)
            try:
                msg = WebDriverWait(driver, 3).until(
                    EC.presence_of_element_located((By.CLASS_NAME, "error-content"))).get_attribute("innerHTML")
            except BaseException:
                self.process_dob()
                self.process_security_question()
                driver.find_element(By.CLASS_NAME, "button-primary").click()
                self.process_password()
            else:
                logger.error(f"操作被苹果拒绝，疑似被风控，错误信息：{msg.strip()}")
                api.update_message(self.username, "操作被苹果拒绝，疑似被风控")
                api.report_proxy_error(config.proxy_id)
                notification("操作被苹果拒绝，疑似被风控")
                get_ip()
                return False
        return True

    def unlock(self):
        if not (self.check()):
            # 选择选项
            try:
                driver.find_element(By.XPATH,
                                    "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/authentication-method/div[2]/div[2]/label/span").click()
            except BaseException:
                logger.error("选择选项失败，无法使用安全问题解锁")
                api.update_message(self.username, "选择选项失败，无法使用安全问题解锁")
                notification("选择选项失败，无法使用安全问题解锁")
                return False
            WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.ID, "action"))).click()
            # 填写生日
            time.sleep(1)
            if self.process_dob():
                if self.process_security_question():
                    time.sleep(2)
                    try:
                        driver.find_element(By.CLASS_NAME, "pwdChange").click()
                    except BaseException:
                        return True
                    # 重置密码
                    return self.process_password()
            return False
        return True

    def login_appleid(self):
        logger.info("开始登录AppleID")
        try:
            driver.get("https://appleid.apple.com/sign-in")
        except BaseException:
            logger.error("登录页面加载失败")
            api.update_message(self.username, "登录页面加载失败")
            notification("登录页面加载失败")
            return False
        try:
            driver.switch_to.alert.accept()
        except BaseException:
            pass
        driver.switch_to.frame(WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.TAG_NAME, "iframe"))))
        WebDriverWait(driver, 10).until(EC.element_to_be_clickable((By.ID, "account_name_text_field"))).send_keys(
            self.username)
        time.sleep(1)
        driver.find_element(By.ID, "account_name_text_field").send_keys(Keys.ENTER)
        WebDriverWait(driver, 5).until(EC.element_to_be_clickable((By.ID, "password_text_field"))).send_keys(
            self.password)
        time.sleep(1)
        driver.find_element(By.ID, "password_text_field").send_keys(Keys.ENTER)
        time.sleep(5)
        try:
            msg = driver.find_element(By.ID, "errMsg").get_attribute("innerHTML")
        except BaseException:
            pass
        else:
            logger.error(f"登录失败，错误信息：\n{msg.strip()}")
            return False
        question_element = WebDriverWait(driver, 5).until(
            EC.presence_of_all_elements_located((By.XPATH, "//*[contains(@class, 'question')]")))
        answer0 = self.get_answer(question_element[1].get_attribute("innerHTML"))
        answer1 = self.get_answer(question_element[2].get_attribute("innerHTML"))
        if answer0 == "" or answer1 == "":
            logger.error("安全问题错误，程序已退出")
            api.update_message(self.username, "请检查安全问题设置是否正确，后端已退出")
            driver.quit()
            exit()
        answer_inputs = WebDriverWait(driver, 10).until(
            EC.presence_of_all_elements_located((By.XPATH, "//*[contains(@class, 'input')]")))
        answer_inputs[0].send_keys(answer0)
        time.sleep(1)
        answer_inputs[1].send_keys(answer1)
        time.sleep(1)
        driver.find_element(By.CSS_SELECTOR, 'button[type="submit"]').click()
        time.sleep(5)
        try:
            driver.find_element(By.CLASS_NAME, "has-errors")
        except BaseException:
            pass
        else:
            logger.error("未找到安全问题对应答案，请检查配置")
            api.update_message(self.username, "未找到安全问题对应答案，请检查配置")
            notification("未找到安全问题对应答案，请检查配置")
            return False
        # 跳过双重验证
        try:
            driver.switch_to.frame(
                WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.TAG_NAME, "iframe"))))
        except BaseException:
            logger.error("跳过双重验证失败")
            return False
        try:
            WebDriverWait(driver, 5).until(EC.presence_of_element_located((By.XPATH,
                                                                           "/html/body/div[1]/appleid-repair/idms-widget/div/div/div/hsa2-enrollment-flow/div/div/idms-step/div/div/div/div[3]/idms-toolbar/div/div[1]/div/button[2]"))).click()
            driver.find_element(By.CLASS_NAME, "nav-cancel").click()
            WebDriverWait(driver, 5).until_not(EC.presence_of_element_located((By.CLASS_NAME, "nav-cancel")))
        except BaseException:
            pass
        driver.switch_to.default_content()
        time.sleep(5)
        logger.info("登录成功")
        return True

    def delete_devices(self):
        # 需要先登录，不能直接执行
        logger.info("开始删除设备")
        # 删除设备
        driver.get("https://appleid.apple.com/account/manage/section/devices")
        WebDriverWait(driver, 10).until_not(EC.presence_of_element_located((By.ID, "loading")))
        time.sleep(2)
        try:
            devices = driver.find_elements(By.CLASS_NAME, "button-expand")
        except BaseException:
            logger.info("没有设备需要删除")
        else:
            logger.info(f"共有{len(devices)}个设备")
            for i in range(len(devices)):
                devices[i].click()
                WebDriverWait(driver, 10).until(
                    EC.presence_of_element_located((By.CLASS_NAME, "button-secondary"))).click()
                WebDriverWait(driver, 10).until(EC.presence_of_element_located(
                    (By.XPATH, "/html/body/aside[2]/div/div[2]/fieldset/div/div/button[2]"))).click()
                WebDriverWait(driver, 10).until_not(
                    EC.presence_of_element_located((By.CLASS_NAME, "button-bar-working")))
                if i != len(devices) - 1:
                    time.sleep(2)
                    devices[i + 1].click()
            logger.info("设备删除完毕")
        return True

    def process_dob(self):
        try:
            WebDriverWait(driver, 5).until(EC.presence_of_element_located((By.CLASS_NAME, "date-input"))).send_keys(
                self.dob)
            time.sleep(1)
            driver.find_element(By.CLASS_NAME, "date-input").send_keys(Keys.ENTER)
        except BaseException:
            return False
        else:
            return True

    def process_security_question(self):
        try:
            question_element = WebDriverWait(driver, 5).until(
                EC.presence_of_all_elements_located((By.CLASS_NAME, "question")))
        except BaseException:
            logger.error("安全问题获取失败，可能是生日错误")
            api.update_message(self.username, "安全问题获取失败，可能是生日错误")
            notification("安全问题获取失败，可能是生日错误")
            return False
        answer0 = self.get_answer(question_element[0].get_attribute("innerHTML"))
        answer1 = self.get_answer(question_element[1].get_attribute("innerHTML"))
        if answer0 == "" or answer1 == "":
            logger.error("未找到安全问题对应答案，请检查配置")
            api.update_message(self.username, "未找到安全问题对应答案，请检查配置")
            notification("未找到安全问题对应答案，请检查配置")
            return False
        answer_inputs = driver.find_elements(By.CLASS_NAME, "generic-input-field")
        answer_inputs[0].send_keys(answer0)
        time.sleep(1)
        answer_inputs[1].send_keys(answer1)
        time.sleep(1)
        answer_inputs[1].send_keys(Keys.ENTER)
        try:
            msg = WebDriverWait(driver, 5).until(
                EC.presence_of_element_located((By.CLASS_NAME, "form-message"))).get_attribute("innerHTML").strip()
        except BaseException:
            return True
        else:
            logger.error(f"安全问题答案错误\n错误信息：{msg}")
            api.update_message(self.username, "安全问题答案错误，请检查配置")
            return False

    def process_password(self):
        try:
            pwd_input_box = WebDriverWait(driver, 5).until(
                EC.presence_of_all_elements_located((By.CLASS_NAME, "override")))
        except BaseException:
            logger.error("密码框获取失败")
            api.update_message(self.username, "密码框获取失败")
            notification("密码框获取失败")
            return False
        new_password = self.generate_password()
        for item in pwd_input_box:
            item.send_keys(new_password)
        time.sleep(1)
        pwd_input_box[-1].send_keys(Keys.ENTER)
        time.sleep(3)
        try:
            driver.find_element(By.XPATH,
                                "/html/body/div[5]/div/div/div[1]/idms-step/div/div/div/div[3]/idms-toolbar/div/div/div/button[1]").click()
        except BaseException:
            pass
        try:
            WebDriverWait(driver, 6).until_not(EC.presence_of_element_located((By.CLASS_NAME, "override")))
        except BaseException:
            logger.error("疑似密码修改失败？看到此报错请与作者反馈。已保存错误信息")
            api.update_message(self.username, "疑似密码修改失败？看到此报错请与作者反馈")
            notification("疑似密码修改失败？看到此报错请与作者反馈")
            # 保存页面到文件
            with open("error.html", "w", encoding="utf-8") as f:
                f.write(driver.page_source)
            # 保存页面截图到文件
            driver.save_screenshot("error.png")
            return False
        self.password = new_password
        logger.info(f"密码修改成功，新密码为{new_password}")
        return True

    def change_password(self):
        if not self.login():
            return False
        logger.info("开始修改密码")
        try:
            driver.find_element(By.XPATH,
                                "//*[@id=\"content\"]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/recovery-options/div[2]/div/div[1]/label/span").click()
            driver.find_element(By.ID, "action").click()
        except BaseException:
            logger.error("现在无法修改密码，可能是二步验证关闭失败")
            api.update_message(self.username, "无法修改密码，可能是二步验证关闭失败")
            notification("无法修改密码，可能是二步验证关闭失败")
            return False
        try:
            WebDriverWait(driver, 5).until(EC.presence_of_element_located((By.XPATH,
                                                                           "//*[@id=\"content\"]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/authentication-method/div[2]/div[2]/label/span"))).click()
            driver.find_element(By.ID, "action").click()
        except BaseException:
            logger.error("无法使用安全问题重设密码，修改失败")
            notification("密码修改失败")
            return False
        if self.process_dob():
            if self.process_security_question():
                if self.process_password():
                    return True
        return False


def notification(content):
    content = f"【{config.username}】{content}"
    if config.tgbot_token != "" and config.tgbot_chatid != "":
        try:
            post(f"https://api.telegram.org/bot{config.tgbot_token}/sendMessage",
                 data={"chat_id": config.tgbot_chatid, "text": content})
        except BaseException as e:
            logger.error(f"Telegram发送消息失败\n错误信息：{e}")
            logger.error("如果机器在大陆，请勿开启Telegram通知")



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
    if config.headless:
        options.add_argument("--headless")
    if config.proxy != "":
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
        return False
    else:
        driver.set_page_load_timeout(30)
        return True


def get_ip():
    global driver
    try:
        driver.get("https://api.ip.sb/ip")
        ip_address = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.TAG_NAME, "pre"))).text
        logger.info(f"当前IP：{ip_address}")
    except BaseException:
        logger.error("无法获取当前IP")


def update_account(username, password):
    global api
    update_result = api.update(username, password)
    if update_result["status"] == "fail":
        logger.error("更新账号失败")
        return False
    else:
        logger.info("更新账号成功")
        return True


def job():
    global api, config, id
    schedule.clear()
    api = API(args.api_url, args.api_key)
    config_result = api.get_config(args.taskid)
    if config_result["status"] == "fail":
        logger.error("从API获取配置失败")
        exit()
    config = Config(config_result)
    id = ID(config.username, config.password, config.dob, config.answer)

    unlock = False
    unlock_success = True
    driver_result = setup_driver()
    logger.info(f"当前账号：{id.username}")
    if not driver_result:
        api.update_message(id.username, "Webdriver调用失败")
        notification("Webdriver调用失败")
    if driver_result and id.login():
        # 检查账号
        if id.check_2fa():
            logger.info("检测到账号开启双重认证，开始解锁")
            unlock_success = id.unlock_2fa()
            unlock = True
        elif not (id.check()):
            logger.info("检测到账号被锁定，开始解锁")
            unlock_success = id.unlock()
            unlock = True
        logger.info("账号检测完毕")

        if unlock_success:
            # 更新账号信息
            if unlock:
                update_account(id.username, id.password)
                notification(f"Apple ID更新成功\n新密码：{id.password}")
            else:
                update_account(id.username, "")

            # 自动重置密码
            if config.enable_auto_update_password:
                if not unlock:
                    logger.info("开始修改密码")
                    reset_pw_result = id.change_password()
                    if reset_pw_result:
                        unlock = True
                        update_account(id.username, id.password)
                        notification(f"Apple ID密码修改成功\n新密码：{id.password}")
                    else:
                        logger.error("修改密码失败")
                        notification("修改密码失败")

            # 自动删除设备
            if config.enable_delete_devices or config.enable_check_password_correct:
                need_login = False
                if not unlock:
                    # 未重置密码，先获取最新密码再执行登录
                    id.password = api.get_password(id.username)
                login_result = id.login_appleid()
                if not login_result and config.enable_check_password_correct:
                    logger.info("密码错误，开始修改密码")
                    reset_pw_result = id.change_password()
                    if reset_pw_result:
                        need_login = True
                        update_account(id.username, id.password)
                        notification(f"Apple ID密码修改成功\n新密码：{id.password}")
                    else:
                        logger.error("修改密码失败")
                        notification("修改密码失败")
                if config.enable_delete_devices:
                    if need_login:
                        login_result = id.login_appleid()
                    if login_result:
                        id.delete_devices()
                    else:
                        logger.error("登录Apple ID失败，无法删除设备")
        else:
            # 解锁失败
            logger.error("解锁失败")
            notification("解锁失败")
    else:
        logger.error("任务执行失败，等待下次检测")
    try:
        driver.quit()
    except BaseException:
        logger.error("Webdriver关闭失败")
    schedule.every(config.check_interval).minutes.do(job)
    logger.info(f"下次任务将在{config.check_interval}分钟后执行")
    return unlock


logger.info(f"{'=' * 80}\n"
            f"{lang_text.launch}\n"
            f"项目地址 https://github.com/pplulee/appleid_auto\n"
            f"Telegram交流群 @appleunblocker")
logger.info("当前版本：v1.44-20230312")
job()
while True:
    schedule.run_pending()
    time.sleep(1)

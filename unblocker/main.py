import argparse
import base64
import logging
import random
import re
import string
import time
import traceback
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

VERSION = "v2.0-20231204"
parser = argparse.ArgumentParser(description="")
parser.add_argument("-api_url", help="API URL")
parser.add_argument("-api_key", help="API key")
parser.add_argument("-taskid", help="Task ID")
parser.add_argument("-lang", help="Output language", default="zh_cn")
parser.add_argument("-debug", help="Debug mode", action="store_true")
args = parser.parse_args()

logger = logging.getLogger()
logger.setLevel('INFO')
BASIC_FORMAT = "%(asctime)s [%(levelname)s] %(message)s"
DATE_FORMAT = "%Y-%m-%d %H:%M:%S"
formatter = logging.Formatter(BASIC_FORMAT, DATE_FORMAT)
chlr = logging.StreamHandler()
chlr.setFormatter(formatter)
logger.addHandler(chlr)

if args.lang == "zh_cn" or args.lang == "":
    from lang import zh_cn as lang
elif args.lang == "en_us":
    from lang import en_us as lang
elif args.lang == "vi_vn":
    from lang import vi_vn as lang
else:
    logger.error("未知语言 | Language not supported")
    exit(1)
lang_text = lang()
debug = args.debug


class API:
    def __init__(self, url, key):
        self.url = url
        self.key = key

    def get_config(self, id):
        try:
            result = loads(post(f"{self.url}/api/get_task_info",
                                verify=False,
                                headers={'key': self.key},
                                data={
                                    "id": id
                                }).text)

        except BaseException as e:
            logger.error(lang_text.ErrorRetrievingConfig)
            logger.error(e)
            return {"status": False}
        else:
            if result["code"] == 200:
                result["data"]["status"] = True
                return result["data"]
            else:
                logger.error(result["msg"])
                return {"status": False}

    def update(self, username, password, status, message):
        try:
            result = loads(post(f"{self.url}/api/update_account",
                                verify=False,
                                headers={'key': self.key},
                                data={
                                    "username": username,
                                    "password": password,
                                    "status": status,
                                    "message": message
                                }).text)
        except BaseException as e:
            logger.error(lang_text.failOnPasswordUpdate)
            logger.error(e)
            return False
        else:
            if result["status"]:
                return True
            else:
                logger.error(result["msg"])
                return False

    def update_message(self, username, message):
        return self.update(username, "", False, message)

    def get_password(self, username):
        try:
            result = loads(
                post(f"{self.url}/api/get_password",
                     verify=False,
                     headers={'key': self.key},
                     data={
                         "username": username,
                     }).text)
        except BaseException as e:
            logger.error(lang_text.failOnRetrievingPassword)
            logger.error(e)
            return ""
        else:
            if result["status"]:
                return result["data"]["password"]
            else:
                logger.error(result["msg"])
                return ""

    def report_proxy_error(self, proxy_id):
        try:
            result = loads(
                post(f"{self.url}/api/report_proxy_error",
                     verify=False,
                     headers={'key': self.key},
                     data={"id": proxy_id}).text)
        except BaseException as e:
            logger.error(lang_text.failOnReportingProxyError)
            logger.error(e)
            return False
        else:
            if not result["status"]:
                logger.error(result["msg"])
            return result["status"]

    def disable_account(self, username):
        try:
            result = loads(
                post(f"{self.url}/api/disable_account",
                     verify=False,
                     headers={'key': self.key},
                     data={"username": username}).text)
        except BaseException as e:
            logger.error(lang_text.failOnDisablingAccount)
            logger.error(e)
            return False
        else:
            return True


class Config:
    def __init__(self, config_result):
        self.password_length = 10
        self.username = config_result["username"]
        self.password = config_result["password"] if "password" in config_result.keys() else "123456"
        self.dob = config_result["dob"]
        self.answer = {config_result["q1"]: config_result["a1"],
                       config_result["q2"]: config_result["a2"],
                       config_result["q3"]: config_result["a3"]}
        self.check_interval = config_result["check_interval"]
        self.webdriver = config_result["webdriver"]
        self.proxy_id = config_result["proxy_id"] if "proxy_id" in config_result.keys() else -1
        self.proxy_type = config_result["proxy_protocol"] if "proxy_protocol" in config_result.keys() else ""
        self.proxy_content = config_result["proxy_content"] if "proxy_content" in config_result.keys() else ""
        self.tg_chat_id = config_result["tg_chat_id"] if "tg_chat_id" in config_result.keys() else ""
        self.tg_bot_token = config_result["tg_bot_token"] if "tg_bot_token" in config_result.keys() else ""
        self.wx_pusher_id = config_result["wx_pusher_id"] if "wx_pusher_id" in config_result.keys() else ""
        self.webhook = config_result["webhook"] if "webhook" in config_result.keys() else ""
        self.enable_check_password_correct = "check_password_correct" in config_result.keys()
        self.enable_delete_devices = "enable_delete_devices" in config_result.keys()
        self.enable_auto_update_password = "enable_auto_update_password" in config_result.keys()
        self.headless = "task_headless" in config_result.keys()
        self.fail_retry = config_result["fail_retry"]
        self.enable = config_result["enable"]
        self.proxy = ""
        if not debug and self.proxy_content != "" and self.proxy_type != "":
            # 新版本代理
            if "url" in self.proxy_type:
                try:
                    self.proxy_type = self.proxy_type.split("+")[0]
                    self.proxy_content = get(self.proxy_content).text
                    self.proxy = self.proxy = self.proxy_type + "://" + self.proxy_content
                except BaseException as e:
                    logger.error(lang_text.failOnRetrievingProxyFromAPI)
                    logger.error(e)
                    self.proxy = ""
                else:
                    logger.info(f"{lang_text.retrievedProxyFromAPI}: {self.proxy}")
            elif self.proxy_type == "socks5" or self.proxy_type == "http":
                self.proxy = self.proxy_type + "://" + self.proxy_content
            else:
                logger.error(lang_text.invalidProxyType)
                self.proxy = ""
        if self.headless:
            logger.info(lang_text.backgroundRunning)
        if self.enable_delete_devices:
            logger.info(lang_text.removeDevice)
        if self.enable_check_password_correct:
            logger.info(lang_text.checkPassword)
        if self.enable_auto_update_password:
            logger.info(lang_text.autoUpdatePassword)
        if self.proxy_id != -1:
            logger.info(f"{lang_text.usingProxyID}: {self.proxy_id}\n{self.proxy}")
        if debug:
            logger.info("已启用调试模式 | Debug mode enabled")
            self.headless = False
            self.webdriver = "local"


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
            WebDriverWait(driver, 10).until(
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
            record_error()
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
            return False

    def process_verify(self):
        # 需要先调用login到达页面
        try:
            img = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.TAG_NAME, "img"))).get_attribute(
                "src").strip().replace('data:image/jpeg;base64,', '')
            img_bytes = base64.b64decode(img)
            code = ocr.classification(img_bytes)
            captcha_element = driver.find_element(By.CLASS_NAME, "captcha-input")
            for char in code:
                captcha_element.send_keys(char)
        except BaseException as e:
            logger.error(lang_text.failOnGettingCaptcha)
            print(e)
            record_error()
            return False
        else:
            return True

    def login(self):
        if not (self.refresh()):
            return False
        try:
            WebDriverWait(driver, 7).until(
                EC.presence_of_element_located((By.CLASS_NAME, "iforgot-apple-id")))
            time.sleep(1)
            input_element = driver.find_element(By.CLASS_NAME, "iforgot-apple-id")
            for char in self.username:
                input_element.send_keys(char)
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
            record_error()
            return False
        while True:
            if not self.process_verify():
                return False
            time.sleep(1)
            WebDriverWait(driver, 5).until(EC.presence_of_element_located((By.CLASS_NAME, "button-primary"))).click()
            try:
                WebDriverWait(driver, 8).until_not(EC.presence_of_element_located((By.CLASS_NAME, "loading")))
            except BaseException:
                logger.error(lang_text.failOnLoadingPage)
                return False
            try:
                # 验证码错误
                WebDriverWait(driver, 3).until(EC.presence_of_element_located((By.XPATH,
                                                                               "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/global-v2/main/idms-flow/div/forgot-password/div/div/div[1]/idms-step/div/div/div/div[2]/div/div[1]/div[2]/div/iforgot-captcha/div/div/div[1]/idms-textbox/idms-error-wrapper/div/idms-error/div/div/span")))
            except BaseException:
                logger.info(lang_text.captchaCorrect)
                break
            else:
                logger.info(lang_text.captchaFail)
                continue

        try:
            msg = WebDriverWait(driver, 3).until(EC.presence_of_element_located((By.XPATH,
                                                                                 "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/global-v2/main/idms-flow/div/forgot-password/div/div/div[1]/idms-step/div/div/div/div[2]/div/div[1]/div[1]/div/idms-textbox/idms-error-wrapper/div/idms-error/div/div/span"))).get_attribute(
                "innerHTML")
        except BaseException:
            logger.info(lang_text.login)
            return True
        else:
            if "not active" in msg:
                logger.error(lang_text.accountNotActive)
                api.update_message(self.username, lang_text.accountNotActive)
                api.disable_account(self.username)
                notification(lang_text.accountNotActive)
            elif "not valid" in msg:
                logger.error(lang_text.accountNotValid)
                api.update_message(self.username, lang_text.accountNotValid)
                api.disable_account(self.username)
                notification(lang_text.accountNotValid)
            elif "Your request could not be completed because of an error" in msg:
                logger.error(f"{lang_text.blocked}")
                api.update_message(self.username, lang_text.blocked)
                notification(lang_text.blocked)
            else:
                logger.error(f"{lang_text.unknownError}: {msg}")
                api.update_message(self.username, lang_text.unknownError)
                notification(lang_text.unknownError)
            record_error()
            return False

    def check(self):
        try:
            driver.find_element(By.XPATH,
                                "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/main/div/authentication-method/div[2]/div/label/span")
        except BaseException:
            try:
                driver.find_element(By.CLASS_NAME, "date-input")
            except BaseException:
                logger.info(lang_text.notLocked)
                return True  # 未被锁定
            else:
                logger.info(lang_text.locked)
                return False  # 被锁定
        else:
            logger.info(lang_text.locked)
            return False  # 被锁定

    def check_2fa(self):
        try:
            driver.find_element(By.ID, "phoneNumber")
        except BaseException:
            logger.info(lang_text.twoStepnotEnabled)
            return False  # 未开启2FA
        else:
            logger.info(lang_text.twoStepEnabled)
            return True  # 已开启2FA

    def unlock_2fa(self):
        try:
            WebDriverWait(driver, 5).until(EC.presence_of_element_located
                                           ((By.CLASS_NAME, "unenroll"))).click()
        except BaseException:
            logger.error(lang_text.cantFindDisable2FA)
            api.update_message(self.username, lang_text.cantFindDisable2FA)
            notification(lang_text.cantFindDisable2FA)
            return False
        WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.XPATH,
                                                                        "/html/body/div[4]/div/div/recovery-unenroll-start/div/idms-step/div/div/div/div[3]/idms-toolbar/div/div/div/button[1]"))).click()
        time.sleep(1)
        try:
            msg = WebDriverWait(driver, 3).until(
                EC.presence_of_element_located((By.CLASS_NAME, "error-content"))).get_attribute("innerHTML")
        except BaseException:
            pass
        else:
            logger.error(f"{lang_text.rejectedByApple}\n{msg.strip()}")
            api.update_message(self.username, lang_text.rejectedByApple)
            api.report_proxy_error(config.proxy_id)
            notification(f"{lang_text.rejectedByApple}")
            return False
        if self.process_dob():
            if self.process_security_question():
                driver.find_element(By.CLASS_NAME, "button-primary").click()
                if self.process_password():
                    return True
        return False

    def unlock(self):
        if not (self.check()):
            try:
                driver.find_element(By.CLASS_NAME, "date-input")
            except BaseException:
                # 选择选项
                try:
                    driver.find_element(By.XPATH,
                                        "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/main/div/authentication-method/div[2]/div[2]/label/span").click()
                except BaseException:
                    logger.error(lang_text.chooseFail)
                    api.update_message(self.username, lang_text.chooseFail)
                    notification(lang_text.chooseFail)
                    record_error()
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
                            pass
                        # 重置密码
                        return self.process_password()
                return False
            else:
                # 填写生日
                if not self.process_dob():
                    return False
                # 选择选项
                try:
                    WebDriverWait(driver, 5).until(EC.presence_of_element_located((By.XPATH,
                                                                                   "/html/body/div[1]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/section/div/authentication-method/div[2]/div[2]/label/span"))).click()
                except BaseException:
                    logger.error(lang_text.chooseFail)
                    api.update_message(self.username, lang_text.chooseFail)
                    notification(lang_text.chooseFail)
                    return False
                WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.ID, "action"))).click()
                if self.process_security_question():
                    time.sleep(2)
                    try:
                        driver.find_element(By.CLASS_NAME, "pwdChange").click()
                    except BaseException:
                        pass
                    # 重置密码
                    return self.process_password()
        return True

    def login_appleid(self):
        logger.info("开始登录AppleID | Start logging in AppleID")
        try:
            driver.get("https://appleid.apple.com/sign-in")
        except BaseException:
            logger.error(lang_text.loginLoadFail)
            api.update_message(self.username, lang_text.loginLoadFail)
            notification(lang_text.loginLoadFail)
            return False
        try:
            driver.switch_to.alert.accept()
        except BaseException:
            pass
        try:
            text = driver.find_element(By.XPATH, "/html/body/center[1]/h1").text
        except BaseException:
            pass
        else:
            logger.error(lang_text.IPBlocked)
            logger.error(text)
            api.update_message(self.username, lang_text.seeLog)
            if config.proxy != "":
                api.report_proxy_error(config.proxy_id)
            notification(lang_text.seeLog)
            record_error()
            return False
        try:
            driver.switch_to.frame(
                WebDriverWait(driver, 30).until(EC.presence_of_element_located((By.TAG_NAME, "iframe"))))
        except BaseException:
            logger.error(lang_text.loginLoadFail)
            api.update_message(self.username, lang_text.loginLoadFail)
            notification(lang_text.loginLoadFail)
            return False
        try:
            WebDriverWait(driver, 30).until(EC.element_to_be_clickable((By.ID, "account_name_text_field")))
            input_element = driver.find_element(By.ID, "account_name_text_field")
            for char in self.username:
                input_element.send_keys(char)
            input_element.send_keys(Keys.ENTER)
        except BaseException:
            logger.error(lang_text.failOnLoadingPage)
            api.update_message(self.username, lang_text.failOnLoadingPage)
            notification(lang_text.failOnLoadingPage)
            record_error()
            return False
        time.sleep(1)
        input_element = WebDriverWait(driver, 5).until(EC.element_to_be_clickable((By.ID, "password_text_field")))
        for char in self.password:
            input_element.send_keys(char)
        time.sleep(1)
        input_element.send_keys(Keys.ENTER)
        time.sleep(5)
        try:
            msg = driver.find_element(By.ID, "errMsg").get_attribute("innerHTML")
        except BaseException:
            # 若未开启删除设备，则不继续登录
            if not config.enable_delete_devices:
                logger.info(lang_text.login)
                return True
        else:
            logger.error(f"{lang_text.LoginFail}\n{msg.strip()}")
            return False
        question_element = WebDriverWait(driver, 20).until(
            EC.presence_of_all_elements_located((By.XPATH, "//*[contains(@class, 'question')]")))
        answer0 = self.get_answer(question_element[1].get_attribute("innerHTML"))
        answer1 = self.get_answer(question_element[2].get_attribute("innerHTML"))
        if answer0 == "" or answer1 == "":
            logger.error(lang_text.answerIncorrect)
            api.update_message(self.username, lang_text.answerIncorrect)
            record_error()
            return False
        answer_inputs = WebDriverWait(driver, 10).until(
            EC.presence_of_all_elements_located((By.XPATH, "//*[contains(@class, 'input')]")))
        for char in answer0:
            answer_inputs[0].send_keys(char)
        time.sleep(1)
        for char in answer1:
            answer_inputs[1].send_keys(char)
        time.sleep(1)
        driver.find_element(By.CSS_SELECTOR, 'button[type="submit"]').click()
        time.sleep(5)
        try:
            driver.find_element(By.CLASS_NAME, "has-errors")
        except BaseException:
            pass
        else:
            logger.error(lang_text.answerNotMatch)
            api.update_message(self.username, lang_text.answerNotMatch)
            notification(lang_text.answerNotMatch)
            return False
        # 跳过双重验证
        try:
            driver.switch_to.frame(
                WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.TAG_NAME, "iframe"))))
        except BaseException:
            logger.error(lang_text.failOnBypass2FA)
            record_error()
            return False
        try:
            WebDriverWait(driver, 5).until(EC.presence_of_element_located((By.XPATH,
                                                                           "/html/body/div[1]/appleid-repair/idms-widget/div/div/div/hsa2-enrollment-flow/div/div/idms-step/div/div/div/div[3]/idms-toolbar/div/div[1]/div/button[2]"))).click()
            driver.find_element(By.CLASS_NAME, "nav-cancel").click()
            WebDriverWait(driver, 5).until_not(EC.presence_of_element_located((By.CLASS_NAME, "nav-cancel")))
        except BaseException:
            pass
        driver.switch_to.default_content()
        logger.info(lang_text.login)
        return True

    def delete_devices(self):
        # 需要先登录，不能直接执行
        logger.info(lang_text.startRemoving)
        # 删除设备
        driver.get("https://appleid.apple.com/account/manage/section/devices")
        try:
            WebDriverWait(driver, 20).until_not(EC.presence_of_element_located((By.ID, "loading")))
        except BaseException:
            logger.error(lang_text.failOnLoadingPage)
            api.update_message(self.username, lang_text.failOnLoadingPage)
            notification(lang_text.failOnLoadingPage)
            record_error()
            return False
        time.sleep(2)
        try:
            devices = driver.find_elements(By.CLASS_NAME, "button-expand")
        except BaseException:
            logger.info(lang_text.noRemoveRequired)
        else:
            logger.info(lang_text.totalDevices(len(devices)))
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
            logger.info(lang_text.finishRemoving)
        return True

    def process_dob(self):
        try:
            WebDriverWait(driver, 5).until(EC.presence_of_element_located((By.CLASS_NAME, "date-input")))
            input_box = driver.find_element(By.CLASS_NAME, "date-input")
            time.sleep(3)
            for char in self.dob:
                input_box.send_keys(char)
                time.sleep(0.1)
            input_box.send_keys(Keys.ENTER)
        except BaseException:
            return False
        else:
            try:
                msg = WebDriverWait(driver, 3).until(
                    EC.presence_of_element_located((By.CLASS_NAME, "form-message"))).get_attribute("innerHTML")
            except BaseException:
                return True
            else:
                logger.error(f"{lang_text.WrongSecurityAnswer}\n{msg.strip()}")
                api.update_message(self.username, lang_text.WrongSecurityAnswer)
                return False

    def process_security_question(self):
        try:
            question_element = WebDriverWait(driver, 5).until(
                EC.presence_of_all_elements_located((By.CLASS_NAME, "question")))
        except BaseException:
            logger.error(lang_text.DOB_Error)
            api.update_message(self.username, lang_text.DOB_Error)
            notification(lang_text.DOB_Error)
            record_error()
            return False
        answer0 = self.get_answer(question_element[0].get_attribute("innerHTML"))
        answer1 = self.get_answer(question_element[1].get_attribute("innerHTML"))
        if answer0 == "" or answer1 == "":
            logger.error(lang_text.answerNotMatch)
            api.update_message(self.username, lang_text.answerNotMatch)
            notification(lang_text.answerNotMatch)
            return False
        answer_inputs = driver.find_elements(By.CLASS_NAME, "generic-input-field")
        for char in answer0:
            answer_inputs[0].send_keys(char)
        time.sleep(1)
        for char in answer1:
            answer_inputs[1].send_keys(char)
        time.sleep(1)
        answer_inputs[1].send_keys(Keys.ENTER)
        try:
            msg = WebDriverWait(driver, 5).until(
                EC.presence_of_element_located((By.CLASS_NAME, "form-message"))).get_attribute("innerHTML").strip()
        except BaseException:
            return True
        else:
            logger.error(f"{lang_text.failOnAnswer}\n{msg}")
            api.update_message(self.username, lang_text.failOnAnswer)
            record_error()
            return False

    def process_password(self):
        try:
            pwd_input_box = WebDriverWait(driver, 5).until(
                EC.presence_of_all_elements_located((By.CLASS_NAME, "form-textbox-input")))
        except BaseException:
            logger.error(lang_text.passwordNotFound)
            api.update_message(self.username, lang_text.passwordNotFound)
            notification(lang_text.passwordNotFound)
            record_error()
            return False
        new_password = self.generate_password()
        for item in pwd_input_box:
            item.send_keys(new_password)
        time.sleep(1)
        pwd_input_box[-1].send_keys(Keys.ENTER)
        time.sleep(3)
        try:
            driver.find_element(By.XPATH,
                                "/html/body/div[4]/div/div/div[1]/idms-step/div/div/div/div[3]/idms-toolbar/div/div/div/button[1]").click()
        except BaseException:
            pass
        try:
            msg = WebDriverWait(driver, 3).until(
                EC.presence_of_element_located((By.CLASS_NAME, "error-content"))).get_attribute("innerHTML")
        except BaseException:
            pass
        else:
            logger.error(f"{lang_text.rejectedByApple}: {msg.strip()}")
            api.update_message(self.username, lang_text.rejectedByApple)
            api.report_proxy_error(config.proxy_id)
            notification(lang_text.rejectedByApple)
            record_error()
            return False
        self.password = new_password
        logger.info(f"{lang_text.passwordUpdated}: {new_password}")
        return True

    def change_password(self):
        if not self.login():
            return False
        logger.info(lang_text.startChangePassword)
        try:
            driver.find_element(By.XPATH,
                                "//*[@id=\"content\"]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/main/div/recovery-options/div[2]/div/div[1]/label/span").click()
            time.sleep(3)
            driver.find_element(By.ID, "action").click()
        except BaseException as e:
            print(e)
            logger.error(lang_text.failOnChangePassword)
            api.update_message(self.username, lang_text.failOnChangePassword)
            notification(lang_text.failOnChangePassword)
            return False
        try:
            WebDriverWait(driver, 5).until(EC.presence_of_element_located((By.CLASS_NAME, "date-input")))
        except BaseException:
            pass
        else:
            if not self.process_dob():
                return False
        try:
            WebDriverWait(driver, 5).until(EC.presence_of_element_located((By.XPATH,
                                                                           "//*[@id=\"content\"]/iforgot-v2/app-container/div/iforgot-body/sa/idms-flow/div/main/div/authentication-method/div[2]/div[2]/label/span"))).click()
            time.sleep(3)
            driver.find_element(By.ID, "action").click()
        except BaseException as e:
            logger.error(e)
            logger.error(lang_text.failToUseSecurityQuestion)
            notification(lang_text.failToUseSecurityQuestion)
            record_error()
            return False
        self.process_dob()
        if self.process_security_question():
            if self.process_password():
                return True
        return False


def notification(content):
    proxies = {
        'http': config.proxy,
        'https': config.proxy,
    } if config.proxy else None

    content = f"【{config.username}】{content}"

    if config.tg_bot_token != "" and config.tg_chat_id != "":
        try:
            post(
                f"https://api.telegram.org/bot{config.tg_bot_token}/sendMessage",
                data={"chat_id": config.tg_chat_id, "text": content},
                proxies=proxies
            )
        except BaseException as e:
            logger.error(f"{lang_text.TGFail}\nError: {e}")
            logger.error(lang_text.cnTG)
    if config.wx_pusher_id != "":
        try:
            post("http://www.pushplus.plus/send", data={"token": config.wx_pusher_id, "content": content},
                 proxies=proxies)
        except BaseException as e:
            logger.error(f"{lang_text.WXFail}\nError: {e}")


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
    # Adding argument to disable the AutomationControlled flag
    options.add_argument("--disable-blink-features=AutomationControlled")
    # Exclude the collection of enable-automation switches
    options.add_experimental_option("excludeSwitches", ["enable-automation"])
    # Turn-off userAutomationExtension
    options.add_experimental_option("useAutomationExtension", False)
    if config.headless:
        options.add_argument("--headless")
    if config.proxy != "":
        options.add_argument(f"--proxy-server={config.proxy}")
    user_agents = [
        # Windows Chrome
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
        # macOS Chrome
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_4_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
        # Linux Chrome
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36'
    ]
    random_index = random.randint(0, len(user_agents) - 1)
    options.add_argument(f"user-agent={user_agents[random_index]}")
    try:
        if config.webdriver != "local":
            driver = webdriver.Remote(command_executor=config.webdriver, options=options)
        else:
            driver = webdriver.Chrome(options=options)
        # Changing the property of the navigator value for webdriver to undefined
        driver.execute_script("Object.defineProperty(navigator, 'webdriver', {get: () => undefined})")
    except BaseException as e:
        logger.error(lang_text.failOnCallingWD)
        logger.error(e)
        return False
    else:
        driver.set_page_load_timeout(30)
        return True


def record_error():
    try:
        # 保存页面到文件
        with open("error.html", "w", encoding="utf-8") as f:
            f.write(driver.page_source)
        # 保存页面截图到文件
        driver.save_screenshot("error.png")
    except BaseException:
        logger.error(lang_text.failOnSavingScreenshot)
    else:
        logger.error(lang_text.screenshotSaved)


def get_ip():
    global driver
    try:
        driver.get("https://api.ip.sb/ip")
        ip_address = WebDriverWait(driver, 5).until(EC.presence_of_element_located((By.TAG_NAME, "pre"))).text
        logger.info(f"IP: {ip_address}")
        return ip_address
    except BaseException:
        try:
            # 尝试ipip.net
            driver.get("https://myip.ipip.net/s")
            ip_address = WebDriverWait(driver, 5).until(EC.presence_of_element_located((By.TAG_NAME, "pre"))).text
            logger.info(f"IP: {ip_address}")
            return ip_address
        except BaseException:
            logger.error(lang_text.getIPFail)
            return ""


def update_account(username, password):
    global api
    if config.webhook != "" and password != "":
        try:
            post(config.webhook, data={"username": username, "password": password})
        except BaseException as e:
            logger.error(f"{lang_text.WebhookFail}\nError: {e}")
    if api.update(username, password, True, lang_text.normal):
        logger.info(lang_text.updateSuccess)
        return True
    else:
        logger.error(lang_text.updateFail)
        return False


def job():
    global api, config, id
    schedule.clear()
    api = API(args.api_url, args.api_key)
    config_result = api.get_config(args.taskid)
    if not config_result["status"]:
        logger.error(lang_text.getAPIFail)
        schedule.every(10).minutes.do(job)
        logger.info(lang_text.nextRun(10))
        return
    config = Config(config_result)
    if (not config.enable) and (not debug):
        # 任务已被禁用
        logger.info(lang_text.taskDisabled)
        schedule.every(10).minutes.do(job)
        logger.info(lang_text.nextRun(10))
        return

    id = ID(config.username, config.password, config.dob, config.answer)
    job_success = True
    driver_result = setup_driver()
    logger.info(f"{lang_text.CurrentAccount}{id.username}")
    if not driver_result:
        api.update_message(id.username, lang_text.failOnCallingWD)
        notification(lang_text.failOnCallingWD)
        job_success = False
    get_ip()
    try:
        if driver_result and id.login():
            origin_password = id.password
            # 检查账号
            if id.check_2fa():
                logger.info(lang_text.twoStepDetected)
                login_result = id.unlock_2fa()
            elif not (id.check()):
                logger.info(lang_text.accountLocked)
                login_result = id.unlock()
            else:
                login_result = True
            logger.info(lang_text.checkComplete)

            # 更新账号信息
            if password_changed := (origin_password != id.password):
                update_account(id.username, id.password)
                notification(f"{lang_text.updateSuccess}\n{lang_text.newPassword}{id.password}")
            elif login_result:
                update_account(id.username, "")

            reset_result = True
            if login_result:
                # 自动重置密码
                if config.enable_auto_update_password:
                    if not password_changed:
                        logger.info(lang_text.startChangePassword)
                        reset_pw_result = id.change_password()
                        if reset_pw_result:
                            update_account(id.username, id.password)
                            notification(f"{lang_text.updateSuccess}\n{lang_text.newPassword}{id.password}")
                        else:
                            logger.error(lang_text.FailToChangePassword)
                            notification(lang_text.FailToChangePassword)
                            reset_result = False

                # 自动删除设备
                if reset_result and (config.enable_delete_devices or config.enable_check_password_correct):
                    need_login = False
                    login_result = id.login_appleid()
                    # 启用自动重置密码，但密码错误
                    if config.enable_auto_update_password and not login_result:
                        logger.error(lang_text.loginFail)
                        record_error()
                    else:
                        if not login_result and config.enable_check_password_correct:
                            logger.info(lang_text.passwordChanged)
                            reset_pw_result = id.change_password()
                            if reset_pw_result:
                                need_login = True
                                update_account(id.username, id.password)
                                notification(f"{lang_text.updateSuccess}\n{lang_text.newPassword}{id.password}")
                            else:
                                logger.error(lang_text.FailToChangePassword)
                                notification(lang_text.FailToChangePassword)
                        if config.enable_delete_devices:
                            if need_login:
                                login_result = id.login_appleid()
                            if login_result:
                                id.delete_devices()
                            else:
                                logger.error(lang_text.LoginFail)
                                record_error()
            else:
                # 解锁失败
                logger.error(lang_text.UnlockFail)
                notification(lang_text.UnlockFail)
                job_success = False
        else:
            logger.error(lang_text.missionFailed)
            job_success = False
    except BaseException:
        logger.error(lang_text.unknownError)
        traceback.print_exc()
        record_error()
        api.update_message(id.username, lang_text.unknownError)
        notification(lang_text.unknownError)
        job_success = False
    try:
        driver.quit()
    except BaseException:
        logger.error(lang_text.WDCloseError)
    if config.fail_retry:
        # 如果任务执行失败，5分钟后再次执行
        next_time = config.check_interval if job_success else 5
    else:
        next_time = config.check_interval
    schedule.every(next_time).minutes.do(job)
    logger.info(lang_text.nextRun(next_time))
    return


logger.info(f"{'=' * 80}\n"
            f"{lang_text.launch}\n"
            f"{lang_text.repoAddress}: https://github.com/pplulee/appleid_auto\n"
            f"{lang_text.TG_Group}: @appleunblocker\n"
            f"{lang_text.proVersion} https://docs.appleidauto.org/\n")
logger.info(f"{lang_text.version}: {VERSION}")
job()
while True:
    schedule.run_pending()
    time.sleep(1)

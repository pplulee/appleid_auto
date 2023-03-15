<h1 align="center">Apple ID One-Click Unlocking Tool</h1>
<p align="center">
    <a href="https://github.com/pplulee/appleid_auto/issues" style="text-decoration:none">
        <img src="https://img.shields.io/github/issues/pplulee/appleid_auto.svg" alt="GitHub issues"/>
    </a>
    <a href="https://github.com/pplulee/appleid_auto/stargazers" style="text-decoration:none" >
        <img src="https://img.shields.io/github/stars/pplulee/appleid_auto.svg" alt="GitHub stars"/>
    </a>
    <a href="https://github.com/pplulee/appleid_auto/network" style="text-decoration:none" >
        <img src="https://img.shields.io/github/forks/pplulee/appleid_auto.svg" alt="GitHub forks"/>
    </a>
    <a href="https://github.com/pplulee/apple_auto/blob/main/LICENSE" style="text-decoration:none" >
        <img src="https://img.shields.io/github/license/pplulee/appleid_auto" alt="GitHub license"/>
    </a>
</p>
<h3 align="center"><a href="README_zh_CN.md" style="text-decoration:none">中文文档</a> | English</h3>
<h3 align="center">Follow the instruction below to hae better experience</h3>  
<h3 align="center">Our project is open-source and will be updated from time to time</h3>


# Basic Introduction

"Manage your Apple ID in a brand-new way" - This is an automated Apple ID detection & unlocking program based on security questions.

The frontend is used to manage accounts, support adding multiple accounts, and provide a display account page.

Support creating a shared page containing multiple accounts and setting a password for the shared page.

The backend checks whether the account is locked at regular intervals. If it is locked or two-step verification is enabled, it will be automatically unlocked, the password will be changed, and the password will be reported to the API.

Log in to Apple ID and automatically delete devices in Apple ID.

Enable proxy pool and Selenium cluster to improve the success rate of unlocking and prevent wind control.


### Reminder:

1. The **backend runs based on docker**, please make sure that docker is installed on the machine;
2. unblocker_manager is the **backend management program**, 
which will get the task list from the API at regular intervals and deploy docker containers (one container for each account);
3. The program **needs to use Chrome webdriver**, 
it is recommended to use the Docker version [selenium/standalone-chrome](https://hub.docker.com/r/selenium/standalone-chrome),
the docker deployment command is as follows, please modify the parameters according to your needs.
```bash
docker run -d --name=webdriver --log-opt max-size=1m --log-opt max-file=1 --shm-size="2g" --restart=always -e SE_NODE_MAX_SESSIONS=10 -e SE_NODE_OVERRIDE_MAX_SESSIONS=true -e SE_SESSION_RETRY_INTERVAL=1 -e SE_VNC_VIEW_ONLY=1 -p 4444:4444 -p 5900:5900 selenium/standalone-chrome
```
4. The program **backend** supports 3 languages currently: English, Simplified Chinese, and Vietnamese. 
The language can be easily set by using the one-click deployment script provided in section [Usage](#Usage).


# Usage

**Please deploy the frontend first, and then install the backend. The backend installation script provides a one-click installation of webdriver** \
If you want to know more about Selenium Grid cluster, please go to [sahuidhsu/selenium-grid-docker](https://github.com/sahuidhsu/selenium-grid-docker) \
The recommended web page running environment is php7.4 & MySQL8.0, theoretically supporting MySQL5.x, other versions of php may not be supported.

1. Download the web page source code from Release and deploy it, import the database (`sql/db.sql`), copy the configuration file `config.bak.php` to `config.php`, and fill in the settings \
   Default account: `admin` password: `admin`
2. After logging in to the website, add the Apple account and fill in the account information
3. Deploy `backend\unblocker_manager.py` (we provide a one-click deployment script, please see below)
4. Check whether `unblocker_manager` successfully obtains the task list
5. Check whether the container is deployed and running normally

### One-click deployment of unblocker_manager (backend + webdriver):

```bash
bash <(curl -Ls https://raw.githubusercontent.com/pplulee/appleid_auto/main/backend/install_unblocker.sh)
```

### Description of security questions:

Questions only need to fill in keywords, such as "birthday", "work", etc., but please note that the **language of the account security question**


# Frontend update

Download the web page source code from Release and overwrite the original files, re-fill in config.php, and import the updated database file (the file beginning with update_).


# Backend update

If the latest version of the backend management script is being used, simply restart the appleauto service to update. If unable to update, the installation script can be re-executed.


# Q&A

The developer's level and ability are limited, and the program may have many bugs. Welcome to submit Issues or Pull Requests, and welcome everyone to join the project!
Telegram group: [@appleunblocker](https://t.me/appleunblocker)


# File Description

- `backend\unblocker_manager.py` Backend management program \
  Description: Used to get the task list from API at regular intervals and deploy docker containers corresponding to task \
  Launch parameters: `-api_url <API address> -api_key <API key> ` (The API address should be in the format of http://xxx.xxx, and should not include a trailing slash or path.)
- `backend\unlocker\main.py` Backend unlock program \
  Description: Unlock the account by changing the password through Webdriver and submit the new password to the API. **This program depends on the API to run** \
  Launch parameters: `-api_url <API地址> -api_key <API key> -taskid <Task ID>`

It is only necessary to deploy the **backend management program**, and the script will automatically obtain the task from the API site and deploy the container. The default synchronization time is 10 minutes (manual synchronization can restart the service) \
If you want to use the **backend unlock program** directly, please use the docker version [sahuidhsu/appleid_auto](https://hub.docker.com/r/sahuidhsu/appleid_auto)

---
# Sponsor the developer
[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/baiyimiao) \
USDT-TRC20: TV1su1RnQny27YEF9WG4DbC8AAz3udt6d4 \
ETH-ERC20：0xea8fbe1559b1eb4b526c3bb69285203969b774c5 \
Entertainment: If you have a need to use the mailbox, please feel free to consult the developer

---

# API Documentation

Path: `/api/` \
Method: `GET` \
All actions need to pass in the `key` parameter, the value is `apikey` in `config.php` \
Return type: `JSON` \
Common return parameters

| parameter | value / type     | description              |
|-----------|------------------|--------------------------|
| `status`  | `success`/`fail` | operation success / fail |
| `message` | `String`         | prompt information       |

Action: `random_sharepage_password` \
Description: Generate a random share page password \
Input parameters:

| parameter | value / type                | description   |
|-----------|-----------------------------|---------------|
| `action`  | `random_sharepage_password` | operation     |
| `id`      | `Int`                       | share page ID |

return parameters:

| parameter  | value / type | description  |
|------------|--------------|--------------|
| `password` | `String`     | new password |

……The rest are waiting to be added (๑•̀ㅂ•́)و✧

---

# JSON API interface

It is possible to obtain account information in JSON format by sharing a page link, which can be used to integrate with other apps. \
The page link refers to the page's code, rather than the entire URL.

API address: `/api/share.php` \
Request method: `GET` \
Input parameters:

| parameter    | value / type | description        |
|--------------|--------------|--------------------|
| `share_link` | `String`     | 分享页代码              |
| `password`   | `String`     | 分享页密码（若未设置密码则不需要）  |

return parameters:

| parameter   | value / type     | description                                       |
|-------------|------------------|---------------------------------------------------|
| `status`    | `success`/`fail` | operation success / fail                          |
| `message`   | `String`         | prompt information                                |
| `accounts`  | `Array`          | List of account information (see the table below) |

Account information:

| parameter     | value / type | description               |
|---------------|--------------|---------------------------|
| `id`          | `Int`        | Account ID                |
| `username`    | `String`     | Account                   |
| `password`    | `String`     | Password                  |
| `status`      | `Bool`       | Account status            |
| `last_check`  | `String`     | Last check time           |
| `remark`      | `String`     | Account front-end remarks |


---
# TODO List

- [x] Auto recognition of verification code
- [x] Check if the account is locked
- [x] Check two-step verification
- [x] Share page supports multiple accounts
- [x] Share page can be opened with password
- [x] Check password correctness
- [x] Delete device
- [x] Change password at regular intervals
- [x] Report password
- [x] Proxy pool
- [x] Telegram Bot notification
- [x] JSON API interface to obtain account information

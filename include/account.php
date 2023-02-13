<?php

class account
{
    var int $id;
    var string $remark;
    var string $username;
    var string $password;
    var string $dob;
    var array $question;
    var int $owner;
    var string $share_link;
    var string $last_check;
    var int $check_interval;
    var string $message;
    var string $frontend_remark;
    var bool $enable_check_password_correct;
    var bool $enable_delete_devices;

    function __construct($id)
    {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM account WHERE id=:id;");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        if ($stmt->rowCount() == 0) {
            $this->id = -1;
        } else {
            $this->id = $id;
            $this->remark = $result["remark"];
            $this->username = $result['username'];
            $this->password = $result['password'];
            $this->dob = $result['dob'];
            $this->question = array(
                $result["question1"] => $result["answer1"],
                $result["question2"] => $result["answer2"],
                $result["question3"] => $result["answer3"]
            );
            $this->owner = $result['owner'];
            $this->share_link = $result['share_link'];
            $this->last_check = $result['last_check'];
            $this->check_interval = $result['check_interval'];
            $this->message = $result['message'];
            $this->frontend_remark = $result['frontend_remark'];
            $this->enable_check_password_correct = $result['enable_check_password_correct'];
            $this->enable_delete_devices = $result['enable_delete_devices'];
        }
    }

    function update($username, $password, $remark, $dob, $question1, $answer1, $question2, $answer2, $question3, $answer3, $owner, $share_link, $check_interval, $frontend_remark, $enable_check_password_correct, $enable_delete_devices)
    {
        global $conn;
        $this->username = $username;
        $this->password = $password;
        $this->remark = $remark;
        $this->dob = $dob;
        $this->question = array(
            $question1 => $answer1,
            $question2 => $answer2,
            $question3 => $answer3
        );
        $this->owner = $owner;
        $this->share_link = $share_link;
        $this->check_interval = $check_interval;
        $this->frontend_remark = $frontend_remark;
        $this->enable_check_password_correct = $enable_check_password_correct;
        $this->enable_delete_devices = $enable_delete_devices;
        $sql = "UPDATE `account` SET `username`=:username, `password`=:password, `remark`=:remark, `dob`=:dob, `question1`=:question1, `answer1`=:answer1, `question2`=:question2, `answer2`=:answer2, `question3`=:question3, `answer3`=:answer3, `owner`=:owner, `share_link`=:share_link, `check_interval`=:check_interval, `frontend_remark`=:frontend_remark, `enable_check_password_correct`=:enable_check_password_correct, `enable_delete_devices`=:enable_delete_devices WHERE `id`=:id;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'username' => $username,
            'password' => $password,
            'remark' => $remark,
            'dob' => $dob,
            'question1' => $question1,
            'answer1' => $answer1,
            'question2' => $question2,
            'answer2' => $answer2,
            'question3' => $question3,
            'answer3' => $answer3,
            'owner' => $owner,
            'share_link' => $share_link,
            'check_interval' => $check_interval,
            'frontend_remark' => $frontend_remark,
            'enable_check_password_correct' => $enable_check_password_correct,
            'enable_delete_devices' => $enable_delete_devices,
            'id' => $this->id
        ]);
    }

    function update_password($password)
    {
        global $conn;
        if ($password != "") {
            $this->password = $password;
            $stmt = $conn->prepare("UPDATE account SET password=:password WHERE id=:id;");
            $stmt->execute(['password' => $password, 'id' => $this->id]);
        }
        $this->update_last_check();
        $this->update_message("正常");
    }

    function update_last_check()
    {
        global $conn;
        $this->last_check = get_time();
        $stmt = $conn->prepare("UPDATE account SET last_check=:last_check WHERE id=:id;");
        $stmt->execute(['last_check' => $this->last_check, 'id' => $this->id]);
    }

    function update_message($message)
    {
        global $conn;
        $this->message = $message;
        $stmt = $conn->prepare("UPDATE account SET message=:message WHERE id=:id;");
        $stmt->execute(['message' => $message, 'id' => $this->id]);
    }

    function delete()
    {
        global $conn;
        // 修改相关分享页面
        $stmt = $conn->prepare("SELECT id,account_list FROM share WHERE locate(:id,account_list);");
        $stmt->execute(['id' => $this->id]);
        if ($stmt->rowCount() != 0) {
            while ($row = $stmt->fetch()) {
                $account_list = explode(",", $row["account_list"]);
                if (sizeof($account_list) == 1 && $account_list[0] == $this->id) {
                    $stmt2 = $conn->prepare("DELETE FROM share WHERE id=:share_id;");
                    $stmt2->execute(['share_id' => $row["id"]]);
                } else {
                    $account_list = array_diff($account_list, array($this->id));
                    $account_list = implode(",", $account_list);
                    $stmt2 = $conn->prepare("UPDATE share SET account_list=:account_list WHERE id=:share_id;");
                    $stmt2->execute(['account_list' => $account_list, 'share_id' => $row["id"]]);
                }
            }
        }

        // 删除账号
        $conn->query("DELETE FROM account WHERE id = '$this->id';");
        $this->id = -1;
    }
}
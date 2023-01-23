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

    function __construct($id)
    {
        global $conn;
        $result = $conn->query("SELECT * FROM account WHERE id='$id';");
        if ($result->num_rows == 0) {
            $this->id = -1;
        } else {
            $result = $result->fetch_assoc();
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
        }
    }

    function update($username, $password, $remark, $dob, $question1, $answer1, $question2, $answer2, $question3, $answer3, $owner, $share_link, $check_interval)
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
        $conn->query("UPDATE account SET username='$username',password='$password',remark='$remark',dob='$dob',question1='$question1',answer1='$answer1',question2='$question2',answer2='$answer2',question3='$question3',answer3='$answer3',owner='$owner',share_link='$share_link', check_interval='$check_interval' WHERE id='$this->id';");
    }

    function update_password($password)
    {
        global $conn;
        if ($password != "") {
            $this->password = $password;
            $conn->query("UPDATE account SET password='$password' WHERE id='$this->id';");
        }
        $this->update_last_check();
    }

    function update_last_check()
    {
        global $conn;
        $this->last_check = get_time();
        $conn->query("UPDATE account SET last_check='$this->last_check' WHERE id='$this->id';");
    }

    function delete()
    {
        global $conn;
        // 修改相关分享页面
        $result = $conn->query("SELECT share_id,account_list FROM share WHERE locate('$this->id',account_list);");
        if ($result->num_rows != 0) {
            while ($row = $result->fetch_assoc()) {
                $account_list = explode(",", $row["account_list"]);
                if (sizeof($account_list) == 1 && $account_list[0] == $this->id) {
                    $conn->query("DELETE FROM share WHERE share_id='{$row["share_id"]}';");
                } else {
                    $account_list = array_diff($account_list, array($this->id));
                    $account_list = implode(",", $account_list);
                    $conn->query("UPDATE share SET account_list='$account_list' WHERE share_id='{$row["share_id"]}';");
                }
            }
        }

        // 删除账号
        $conn->query("DELETE FROM account WHERE id = '$this->id';");
        $this->id = -1;
    }
}
<?php

class task
{
    var int $id;
    var int $account_id;
    var int $check_interval;
    var bool $tgbot_enable;
    var string $tgbot_chatid = "";
    var string $tgbot_token = "";
    var int $owner;

    function __construct($task_id)
    {
        global $conn;
        $result = $conn->query("SELECT * FROM task WHERE id='$task_id';");
        if ($result->num_rows == 0) {
            $this->id = -1;
        } else {
            $result = $result->fetch_assoc();
            $this->id = $task_id;
            $this->account_id = $result['account_id'];
            $this->check_interval = $result['check_interval'];
            $this->owner = $result['owner'];
            if ($result['tgbot_chatid'] != "" && $result['tgbot_token'] != "") {
                $this->tgbot_enable = true;
                $this->tgbot_chatid = $result['tgbot_chatid'];
                $this->tgbot_token = $result['tgbot_token'];
            } else {
                $this->tgbot_enable = false;
            }
        }
    }

    function update($account_id, $check_interval, $tgbot_chatid, $tgbot_token, $owner)
    {
        global $conn;
        $this->account_id = $account_id;
        $this->check_interval = $check_interval;
        $this->tgbot_chatid = $tgbot_chatid;
        $this->tgbot_token = $tgbot_token;
        $this->owner = $owner;
        if ($tgbot_chatid != "" && $tgbot_token != "") {
            $this->tgbot_enable = true;
        } else {
            $this->tgbot_enable = false;
        }
        if ($this->id != -1) {
            $this->delete();
        }
        $conn->query("INSERT INTO task (account_id,check_interval,tgbot_chatid,tgbot_token,owner) VALUES ('{$this->account_id}','{$this->check_interval}','{$this->tgbot_chatid}','{$this->tgbot_token}','{$this->owner}');");
        $this->id = mysqli_insert_id($conn);
    }

    function delete()
    {
        global $conn;
        $conn->query("DELETE FROM task WHERE id = '$this->id';");
        $this->id = -1;
    }
}
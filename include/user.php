<?php

class user
{
    var int $user_id;
    var string $username;
    var int $is_admin;

    function __construct($user_id)
    {
        global $conn;
        $result = $conn->query("SELECT username,is_admin FROM user WHERE id='{$user_id}';");
        if ($result->num_rows == 0) {
            $this->user_id = -1;
        } else {
            $result = $result->fetch_assoc();
            $this->user_id = $user_id;
            $this->username = $result['username'];
            $this->is_admin = $result['is_admin'];
        }
    }

    function update($username, $isadmin)
    {
        global $conn;
        $this->username = $username;
        $this->is_admin = $isadmin;
        $conn->query("UPDATE user SET username='{$this->username}',is_admin='{$this->is_admin}' WHERE id='{$this->user_id}';");
    }

    function change_password($password)
    {
        global $conn;
        $password = password_hash($password, PASSWORD_DEFAULT);
        $conn->query("UPDATE user SET password='{$password}' WHERE id='{$this->user_id}';");
    }

    function delete_account()
    {
        global $conn;
        $conn->query("DELETE FROM user WHERE id='{$this->user_id}';");
    }
}
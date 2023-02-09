<?php

class user
{
    var int $user_id;
    var string $username;
    var int $is_admin;

    function __construct($user_id)
    {
        global $conn;
        $stmt = $conn->prepare("SELECT `username`,`is_admin` FROM user WHERE id=:id;");
        $stmt->execute(['id' => $user_id]);
        if ($stmt->rowCount() == 0) {
            $this->user_id = -1;
        } else {
            $result = $stmt->fetch();
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
        $stmt = $conn->prepare("UPDATE user SET `username`=:username,is_admin=:is_admin WHERE id=:id;");
        $stmt->execute(['username' => $username, 'is_admin' => $isadmin, 'id' => $this->user_id]);
    }

    function change_password($password)
    {
        global $conn;
        $password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE user SET `password`=:password WHERE id=:id;");
        $stmt->execute(['password' => $password, 'id' => $this->user_id]);
    }

    function delete_account()
    {
        global $conn;
        $stmt = $conn->prepare("DELETE FROM account WHERE `owner`=:owner;");
        $stmt->execute(['owner' => $this->user_id]);
    }
}
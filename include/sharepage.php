<?php

class sharepage
{
    var int $id;
    var string $share_link;
    var string $password;
    var array $account_list;
    var int $owner;

    function __construct($id){
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM share WHERE id=:id;");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        if ($stmt->rowCount() == 0) {
            $this->id = -1;
        } else {
            $this->id = $id;
            $this->share_link = $result['share_link'];
            $this->account_list = explode(",", $result['account_list']);
            $this->password = $result['password']==null?"":$result['password'];
            $this->owner = $result['owner'];
        }
    }

    function update($share_link,$password,array $account_list, $owner){
        global $conn;
        $account_list_str = implode(",", $account_list);
        $stmt = $conn->prepare("UPDATE share SET share_link=:share_link, account_list=:account_list, owner=:owner, password=:password WHERE id=:id;");
        $stmt->execute(['share_link' => $share_link, 'account_list' => $account_list_str, 'owner' => $owner, 'id' => $this->id, 'password' => $password]);
    }

    function delete(){
        global $conn;
        $stmt = $conn->prepare("DELETE FROM share WHERE id=:id;");
        $stmt->execute(['id' => $this->id]);
    }

    function randomPassword(){
        global $conn;
        $this->password = random_string(10);
        $stmt = $conn->prepare("UPDATE share SET password=:password WHERE id=:id;");
        $stmt->execute(['password' => $this->password, 'id' => $this->id]);
        return $this->password;
    }
}
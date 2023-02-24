<?php

class sharepage
{
    var int $id;
    var string $share_link;
    var string $password;
    var array $account_list;
    var int $owner;
    var string $html;

    function __construct($id)
    {
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
            $this->password = $result['password'] == null ? "" : $result['password'];
            $this->owner = $result['owner'];
            $this->html = htmlspecialchars_decode($result['html']);
        }
    }

    function update($share_link, $password, array $account_list, $owner, $html): void
    {
        global $conn;
        $account_list_str = implode(",", $account_list);
        $stmt = $conn->prepare("UPDATE share SET share_link=:share_link, account_list=:account_list, owner=:owner, password=:password, html=:html WHERE id=:id;");
        $this->share_link = $share_link;
        $this->password = $password;
        $this->account_list = $account_list;
        $this->owner = $owner;
        $this->html = htmlspecialchars($html);
        $stmt->execute(['share_link' => $this->share_link, 'account_list' => $account_list_str, 'owner' => $this->owner, 'id' => $this->id, 'password' => $this->password, 'html' => $this->html]);
    }

    function delete(): void
    {
        global $conn;
        $stmt = $conn->prepare("DELETE FROM share WHERE id=:id;");
        $stmt->execute(['id' => $this->id]);
    }

    function randomPassword(): string
    {
        global $conn;
        $this->password = random_string(10);
        $stmt = $conn->prepare("UPDATE share SET password=:password WHERE id=:id;");
        $stmt->execute(['password' => $this->password, 'id' => $this->id]);
        return $this->password;
    }
}
<?php

class proxy
{
    var int $id;
    var string $protocol;
    var string $content;
    var int $owner;
    var string $last_use;
    var bool $status;

    function __construct($id)
    {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM proxy WHERE id=:id;");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        if ($stmt->rowCount() == 0) {
            $this->id = -1;
            $this->protocol = "";
            $this->content = "";
        } else {
            $this->id = $id;
            $this->protocol = $result['protocol'];
            $this->content = $result['content'];
            $this->owner = $result['owner'];
            $this->last_use = $result['last_use'];
            $this->status = $result['status'];
        }
    }

    function update($protocol, $content, $owner, $status): void
    {
        global $conn;
        $stmt = $conn->prepare("UPDATE proxy SET protocol=:protocol, content=:content, owner=:owner, status=:status WHERE id=:id;");
        $this->protocol = $protocol;
        $this->content = $content;
        $this->status = $status;
        $this->owner = $owner;
        $stmt->execute([
            'protocol' => $this->protocol,
            'content' => $this->content,
            'status' => $this->status ? 1 : 0,
            'owner' => $this->owner,
            'id' => $this->id]);
    }

    function delete(): void
    {
        global $conn;
        $stmt = $conn->prepare("DELETE FROM proxy WHERE id=:id;");
        $stmt->execute(['id' => $this->id]);
    }

    function set_enable(): void
    {
        global $conn;
        $stmt = $conn->prepare("UPDATE proxy SET status=1 WHERE id=:id;");
        $this->status = true;
        $stmt->execute(['id' => $this->id]);
    }

    function set_disable(): void
    {
        global $conn;
        $stmt = $conn->prepare("UPDATE proxy SET status=0 WHERE id=:id;");
        $this->status = false;
        $stmt->execute(['id' => $this->id]);
    }

    function update_use(): void
    {
        global $conn;
        $stmt = $conn->prepare("UPDATE proxy SET last_use=:last_use WHERE id=:id;");
        $this->last_use = date("Y-m-d H:i:s");
        $stmt->execute(['last_use' => $this->last_use, 'id' => $this->id]);
    }
}
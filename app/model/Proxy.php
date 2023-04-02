<?php
declare (strict_types=1);

namespace app\model;

use think\Model;
use think\facade\Db;

/**
 * @mixin Model
 */
class Proxy extends Model
{
    protected $table = 'proxy';
    protected $pk = 'id';

    public function addProxy($data): bool
    {
        $proxy = new Proxy();
        $proxy->create($data);
        return true;
    }

    public function updateProxy($id, $data): bool
    {
        if (!$this) {
            $proxy = $this->fetch($id);
        } else {
            $proxy = $this;
        }
        if (!$proxy) {
            return false;
        }
        $proxy->update($data, ['id' => $id]);
        return true;
    }

    public function updateUse($id): bool
    {
        if (!$this) {
            $proxy = $this->fetch($id);
        } else {
            $proxy = $this;
        }
        if (!$proxy) {
            return false;
        }
        Db::table('proxy')
            ->where('id', $id)
            ->update(['last_use' => date('Y-m-d H:i:s')]);
        return true;
    }

    public function setDisable($id): bool
    {
        if (!$this) {
            $proxy = $this->fetch($id);
        } else {
            $proxy = $this;
        }
        if (!$proxy) {
            return false;
        }
        Db::table('proxy')
            ->where('id', $id)
            ->update(['status' => 0]);
        return true;
    }

    public function fetch($id): ?Proxy
    {
        return $this->where('id', $id)->find();
    }

    public function deleteProxy($id): bool
    {
        if (!$this) {
            $proxy = $this->fetch($id);
        } else {
            $proxy = $this;
        }
        if (!$proxy) {
            return false;
        }
        $proxy->delete();
        return true;
    }
}

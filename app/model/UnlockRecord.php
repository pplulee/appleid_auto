<?php
declare (strict_types=1);

namespace app\model;

use think\facade\Db;
use think\Model;

/**
 * @mixin Model
 */
class UnlockRecord extends Model
{
    protected $table = 'unlock_record';
    protected $pk = 'id';

    public function truncate()
    {
        $db = Db::connect();
        $db->execute('TRUNCATE TABLE ' . $this->table);
    }

    public function addRecord($data)
    {
        $data['time'] = date('Y-m-d H:i:s');
        $this->create($data);
    }
}

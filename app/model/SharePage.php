<?php
declare (strict_types=1);

namespace app\model;

use think\Model;

/**
 * @mixin Model
 */
class SharePage extends Model
{
    protected $table = 'share';
    protected $pk = 'id';

    public function addSharePage($data): bool
    {
        if ($this->fetchByLink($data['share_link'])) {
            return false;
        }
        $share = new SharePage();
        $share->create($data);
        return true;
    }

    public function fetchByLink($link): ?SharePage
    {
        return $this->where('share_link', $link)->find();
    }

    public function updateSharePage($id, $data): bool
    {
        if (!$this) {
            $share = $this->fetch($id);
        } else {
            $share = $this;
        }
        if (!$share) {
            return false;
        }
        $share->update($data, ['id' => $id]);
        return true;
    }

    public function fetch($id): ?SharePage
    {
        $share = $this->where('id', $id)->find();
        if (!$share) return null;
        $share->account_list = array_map('intval', explode(",", $share->account_list));
        return $share;
    }

    public function deleteSharePage($id): bool
    {
        if (!$this) {
            $share = $this->fetch($id);
        } else {
            $share = $this;
        }
        if (!$share) {
            return false;
        }
        return $share->delete();
    }
}

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
        if ($this->fetchByLink($data['link'])) {
            return false;
        }
        if (isset($data['account_list'])) {
            $data['account_list'] = implode(",", $data['account_list']);
        } else {
            return false;
        }
        $share = new SharePage();
        $share->create($data);
        return true;
    }

    public function fetchByLink($link): ?SharePage
    {
        return $this->where('link', $link)->find();
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
        if (isset($data['account_list'])) {
            $data['account_list'] = implode(",", $data['account_list']);
        } else {
            return false;
        }
        $share->update($data, ['id' => $id]);
        return true;
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

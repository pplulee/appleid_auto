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

    public function addSharePage($data): array
    {
        if ($this->fetchByLink($data['share_link'])) {
            return ['status' => false, 'msg' => '分享链接已存在'];
        }
        $share = new SharePage();
        $share->create($data);
        return ['status' => true, 'msg' => '添加成功'];
    }

    public function fetchByLink($link): ?SharePage
    {
        $share = $this->where('share_link', $link)->find();
        if (!$share) return null;
        $share->account_list = array_map('intval', explode(",", $share->account_list));
        return $share;
    }

    public function updateSharePage($id, $data): array
    {
        if (!$this) {
            $share = $this->fetch($id);
        } else {
            $share = $this;
        }
        if (!$share) {
            return ['status' => false, 'msg' => '分享链接不存在'];
        }
        // 检查链接重复
        if ($share->share_link != $data['share_link']) {
            if ($this->fetchByLink($data['share_link'])) {
                return ['status' => false, 'msg' => '分享链接已存在'];
            }
        }
        $share->update($data, ['id' => $id]);
        return ['status' => true, 'msg' => '修改成功'];
    }

    public function fetch($id): ?SharePage
    {
        $share = $this->where('id', $id)->find();
        if (!$share) return null;
        $share->account_list = array_map('intval', explode(",", $share->account_list));
        return $share;
    }

    public function deleteSharePage($id): array
    {
        if (!$this) {
            $share = $this->fetch($id);
        } else {
            $share = $this;
        }
        if (!$share) {
            return ['status' => false, 'msg' => '分享链接不存在'];
        }
        $result = $share->delete();
        return ['status' => $result, 'msg' => $result ? '删除成功' : '删除失败'];
    }
}

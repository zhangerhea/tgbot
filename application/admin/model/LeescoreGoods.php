<?php

namespace app\admin\model;

use think\Model;
use app\admin\model\Category;

class LeescoreGoods extends Model
{
    // 表名
    protected $name = 'leescore_goods';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    protected $model = null;
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'type_text',
        'status_text',
        'firsttime_text',
        'lasttime_text',
        'flag_text',
    ];


    protected static function init()
    {

        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    public function getLeescoreGoods()
    {
        return $this->belongsTo('leescoreCategory', 'category_id')->field("name as scName,id,category_id as pcid");
    }

    public function getTypeList()
    {
        return ['0' => __('Type normal'), '1' => __('Type 1')];
    }

    public function getPayTypeList()
    {
        return ['0' => __('score mode'), '1' => __('money mode'), '2' => __('hunhe mode')];
    }

    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2')];
    }

    public function getFlagList()
    {
        return ['index' => __('Flag 0'), 'hot' => __('Flag 1'), 'recommend' => __('Flag 2'), 'new' => __('Flag 3')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['type'];
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getFirsttimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['firsttime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getLasttimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['lasttime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getFlagTextAttr($value, $data)
    {
        $value = $value ? $value : $data['flag'];
        $valueArr = explode(',', $value);
        $list = $this->getFlagList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }

    protected function setFlagAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    protected function setFirsttimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setLasttimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}

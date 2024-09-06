<?php

namespace app\admin\model;

use think\Model;

class LeescoreOrder extends Model
{
    // 表名
    protected $name = 'leescore_order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $payTime = 'paytime';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
        'status_text',
        'paytime_text'
    ];

    public function user()
    {
        return $this->belongsTo('User', 'uid')->setEagerlyType(0);
    }

    public function getOrderGoods()
    {
        return $this->hasMany('leescoreOrderGoods', 'order_id');
    }

    public function addressInfo()
    {
        return $this->belongsTo('Leeorderadd', 'id', 'order_id');
    }

    public function getStatusList()
    {
        return ['-2' => __('Status -2'), '-1' => __('Status -1'), '0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2'), '3' => __('Status 3'), '4' => __('Status 4'), '5' => __('Status 5')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getPaytimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['paytime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : __('Status 0');
    }

    protected function setPaytimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : '$value';
    }


}

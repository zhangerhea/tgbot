<?php

namespace app\admin\model;

use think\Model;

class LeescoreOrderGoods extends Model
{
    // 表名
    protected $name = 'leescore_order_goods';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    // 追加属性
    protected $append = [
    ];

    public function getOrderGoods($id = 0)
    {
        $rows = LeescoreOrderGoods::where("order_id", $id)->select();
        return $rows;
    }
}

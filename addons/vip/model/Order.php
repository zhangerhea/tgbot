<?php

namespace addons\vip\model;

use think\Model;

class Order extends Model
{

    // 表名
    protected $name = 'vip_order';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
    ];

    public function user()
    {
        return $this->belongsTo('\app\common\model\User', "user_id", "id");
    }

    public function vip()
    {
        return $this->belongsTo('\addons\vip\model\Vip', "vip_id", "id");
    }

    public function record()
    {
        return $this->belongsTo('\addons\vip\model\Record', "record_id", "id");
    }

}

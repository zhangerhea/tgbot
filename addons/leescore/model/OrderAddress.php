<?php

namespace addons\leescore\model;

use think\Model;

/**
 * 订单地址模型
 */
class OrderAddress Extends Model
{

    protected $name = "leescore_order_address";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];
    protected static $config = [];

    //自定义初始化
    protected static function init()
    {
        $config = get_addon_config('leescore');
        self::$config = $config;
    }

}

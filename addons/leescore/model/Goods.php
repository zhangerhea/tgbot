<?php

namespace addons\leescore\model;

use think\Model;
use think\Db;

/**
 * Goods
 * 商品模型
 */
class Goods Extends Model
{

    protected $name = "leescore_goods";
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

    public function getGoodsDetail($id)
    {
        $goods = Goods::get($id);
        return $goods;
    }
}

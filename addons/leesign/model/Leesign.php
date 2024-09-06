<?php

namespace addons\leesign\model;

use think\Model;

/**
 * 签到模型
 */
class Leesign Extends Model
{

    protected $name = "leesign";
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
        $config = get_addon_config('cms');
        self::$config = $config;
    }

}

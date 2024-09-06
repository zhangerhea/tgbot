<?php

namespace addons\leescore\model;

use think\Model;

/**
 * Link
 * 链接模型
 */
class Link Extends Model
{

    protected $name = "leescore_link";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = true;
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];
    protected static $config = [];
}

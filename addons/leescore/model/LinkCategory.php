<?php

namespace addons\leescore\model;

use think\Model;
use addons\leescore\model\Link;

/**
 * LinkCategory
 * 链接分类模型
 */
class LinkCategory Extends Model
{

    protected $resultSetType = 'collection';
    protected $name = "leescore_link_category";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = true;
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];
    protected static $config = [];

    public function getLinkCategory($type = '')
    {
        $w['switch'] = '1';
        $type != '' && $w['type'] = $type;
        return LinkCategory::where($w)->select();
    }

    public function getLinkData($type = '2')
    {
        $w['switch'] = '1';
        $w['type'] = $type;
        $linkcate = LinkCategory::where($w)->with('children')->select()->toArray();
        return $linkcate;
    }

    public function children()
    {
        return $this->hasMany('Link', 'typeid');
    }
}

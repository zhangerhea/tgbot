<?php

namespace addons\leescore\model;

use think\Model;
use think\Collection;

/**
 * Ads
 * 广告模型
 */
class Ads Extends Model
{

    protected $name = "leescore_ads";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];
    protected static $config = [];

    // 设置返回数据集的对象名
    protected $resultSetType = 'collection';

    //自定义初始化
    protected static function init()
    {
        $config = get_addon_config('leescore');
        self::$config = $config;
    }

    /**
     * 获取广告位信息
     * @param $position string 广告位置: slider=图片轮播, activity = 活动, other = 其它
     * @return false|\PDOStatement|string|Collection $data object 查询结果对象
     */
    public function getAdsList($position = 'slider')
    {
        $w['position'] = ['eq', $position];
        $w['status'] = ['eq', 1];
        $w['starttime'] = ['elt', time()];
        $w['endtime'] = ['egt', time()];
        return Ads::where($w)->select();
    }
}

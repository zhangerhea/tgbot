<?php

namespace addons\leescore\model;

use think\Model;
use think\template\taglib\Cx;

/**
 * Category
 * 分类模型
 */
class Category Extends Model
{

    protected $resultSetType = 'collection';
    protected $name = "leescore_category";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = true;
    protected $updateTime = false;
    protected $getCateGoodsData = false;
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

    /**
     * 获取菜单（2级）
     * @param int $limit     获取记录数
     * @param int $is_mobile 显示类型: 1=仅移动端
     * @return array $data 分类数据和内容
     */
    public function getNavandGoods($is_mobile = '', $limit = 1, $is_data = false)
    {
        !empty($is_mobile) && $w['is_mobile'] = $is_mobile;
        $is_data == true && $this->getCateGoodsData = true;
        $w['status'] = 'normal';
        $w['category_id'] = 0;
        $data = Category::with('Navs')->order("weigh asc")->where($w)->limit($limit)->select()->toArray();
        return $data;
    }

    public function Navs()
    {
        if ($this->getCateGoodsData) {
            $lists = $this->hasMany('Category', 'category_id')->with('Goods');
        } else {
            $lists = $this->hasMany('Category', 'category_id')->with('getCateNext');
        }
        return $lists;
    }

    public function Goods()
    {
        return $this->hasMany('Goods', 'id', 'category_id');
    }

    public function getCateNext()
    {
        return $this->hasMany('Category', 'id', 'category_id');
    }
}

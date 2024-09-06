<?php

namespace addons\leescore\model;

use think\Model;

/**
 * Cart
 * 购物车模型
 */
class Cart Extends Model
{

    protected $name = "leescore_cart";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = true;
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

    public function goodsDetail()
    {
        return $this->belongsTo('Goods', 'goods_id');
    }

    public function addressInfo()
    {
        return $this->belongsTo('Address', 'address_id');
    }

    public function getUserCartTotal($userid)
    {
        $total = Cart::where("uid", "=", $userid)->count();
        return $total;
    }

    public function getCart($id)
    {
        return Cart::get($id);
    }

    // Cart API
    public function Goods()
    {
        return $this->belongsTo('Goods', 'goods_id', 'id')->setEagerlyType(0);
    }

    public function saveCart($number, $cid)
    {
        $model = new Cart();
        return $model->save(['number' => $number], ["id" => $cid]);
    }

    public function getUserCartInfo($goods_id, $userid)
    {
        return Cart::get(['goods_id' => $goods_id, 'uid' => $userid]);
    }
}

<?php

namespace addons\leescore\model;

use think\Model;
use addons\leescore\model\Order;
use app\common\model\User;

/**
 * OrderGoods
 * 订单商品模型
 */
class OrderGoods Extends Model
{

    protected $name = "leescore_order_goods";
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

    public function getPayUsers($goods_id)
    {
        $model = new OrderGoods();
        $orders = $model->name("leescore_order_goods")
            ->alias('og')
            ->join('leescore_order o', 'o.id = og.order_id', 'inner')
            ->where(["og.goods_id" => $goods_id, "o.status" => ['egt', 1]])
            ->field('og.*, o.id as oid, o.status, o.uid')
            ->limit(14)
            ->select();
        $users = [];
        foreach ($orders as $k => $v) {
            $users[] = User::where('id', $v['uid'])->find();
        }

        return $users;
    }
}

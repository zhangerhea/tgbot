<?php

namespace addons\leescore\model;

use think\Model;

/**
 * 订单模型
 */
class Order Extends Model
{

    protected $name = "leescore_order";
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

    public function addressInfo()
    {
        return $this->belongsTo('order_address', 'id', 'order_id');
    }

    public function orderGoods()
    {
        return $this->hasMany('order_goods', 'order_id');
    }


    //用户个人订单管理列表
    public function getOrderList($w, $paramId)
    {
        $order_list = Order::where($w)->order('createtime desc')->paginate(10, false, ['query' => $paramId])->each(function ($val, $key) {
            $order_goods_lists = OrderGoods::all(['order_id' => $val['id']]);
            $val['order_goods'] = $order_goods_lists;
            $val->orderGoods;
        });
        return $order_list;
    }

    public function getPaytimeAttr($value)
    {
        $paytime = !empty($value) ? date('Y-m-d H:i', $value) : '-';
        return $paytime;
    }

    //用户个人订单详情
    public function getOrderDetail($w)
    {

        $order_details = Order::where($w)->with('orderGoods,addressInfo')->find();
        //dump($order_details->addressInfo);
        return $order_details;
    }
}

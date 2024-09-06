<?php

namespace addons\vip\model;

use think\Model;

class Vip extends Model
{

    // 表名
    protected $name = 'vip';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
    ];
    protected $type = [
    ];

    public function getImageAttr($value, $data)
    {
        $value = cdnurl($value, true);
        return $value;
    }

    public function getPricedataAttr($value, $data)
    {
        return $value ? (array)json_decode($value, true) : [];
    }

    public function getRightdataAttr($value, $data)
    {
        $result = $value ? (array)json_decode($value, true) : [];
        foreach ($result as $index => &$item) {
            $item['image'] = cdnurl($item['image'], true);
        }
        return $result;
    }

    public function getPriceByDays($days = 1)
    {
        $price = null;
        $pricedata = $this->pricedata ?: [];
        foreach ($pricedata as $index => $pricedatum) {
            if ($pricedatum['days'] == $days) {
                $price = $pricedatum['price'];
                break;
            }
        }
        return $price;
    }

    public function getDefaultpriceAttr($value, $data)
    {
        if (isset($this->data['defaultprice'])) {
            return $this->data['defaultprice'];
        }
        $defaultprice = [];
        $pricedata = $this->pricedata ?: [];
        foreach ($pricedata as $index => $pricedatum) {
            if ($index == 0) {
                $defaultprice = $pricedatum;
            }
            if (isset($pricedatum['default']) && $pricedatum['default']) {
                $defaultprice = $pricedatum;
                break;
            }
        }
        return $defaultprice;
    }

    public function user()
    {
        return $this->belongsTo('\app\common\model\User', "user_id", "id");
    }

}

<?php

namespace app\admin\model\vip;

use addons\vip\library\Service;
use think\Db;
use think\Exception;
use think\Model;


class Order extends Model
{

    // 表名
    protected $name = 'vip_order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'paytime_text',
        'status_text'
    ];

    public function getOriginData()
    {
        return $this->origin;
    }

    public static function init()
    {
        self::beforeUpdate(function ($row) {
            $changedData = $row->getChangedData();
            $originData = $row->getOriginData();
            if (isset($changedData['status'])) {
                if ($changedData['status'] == 'paid') {
                    $recordInfo = Record::get($row['record_id']);
                    $userVipInfo = Service::getVipInfo($row['user_id']);
                    if ($userVipInfo && $recordInfo && $userVipInfo['level'] > $recordInfo['level']) {
                        throw new Exception("用户当前VIP等级过高，无法修改");
                    }
                }
            }
        });
        self::afterUpdate(function ($row) {
            $changedData = $row->getChangedData();
            $originData = $row->getOriginData();
            if (isset($changedData['status'])) {
                if ($changedData['status'] == 'paid') {
                    Db::name("vip_order")->where('id', $row['id'])->update(['status' => $originData['status']]);
                    \addons\vip\library\Order::settle($row['orderid'], $row['amount']);
                } elseif ($originData['status'] == 'paid') {
                    Db::name("vip_order")->where('id', $row['id'])->update(['status' => $originData['status']]);
                    \addons\vip\library\Order::unsettle($row['orderid']);
                }
            }
        });
    }

    public function getStatusList()
    {
        return ['created' => __('Status created'), 'paid' => __('Status paid'), 'expired' => __('Status expired')];
    }


    public function getPaytimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['paytime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }

    protected function setPaytimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function user()
    {
        return $this->belongsTo('\app\common\model\User', "user_id", "id", [], 'LEFT')->setEagerlyType(0);
    }

    public function vip()
    {
        return $this->belongsTo('Vip', "vip_id", "id", [], 'LEFT')->setEagerlyType(0);
    }


}

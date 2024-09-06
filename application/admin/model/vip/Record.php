<?php

namespace app\admin\model\vip;

use addons\vip\library\Service;
use think\Db;
use think\Exception;
use think\Model;


class Record extends Model
{

    // 表名
    protected $name = 'vip_record';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'expiretime_text',
        'status_text'
    ];

    public function getOriginData()
    {
        return $this->origin;
    }

    public static function init()
    {
        self::beforeInsert(function ($row) {
            $userVipInfo = Service::getVipInfo($row['user_id']);
            $vipInfo = Vip::get($row['vip_id']);
            if (in_array($row['status'], ['active'])) {
                if (isset($userVipInfo['level']) && $userVipInfo['level'] >= $vipInfo['level']) {
                    throw new Exception("当前用户VIP等级过高，无需添加");
                }
            }
            if (!$row['days']) {
                throw new Exception("VIP天数不能为空");
            }
            $row['level'] = $vipInfo['level'];
        });

        self::afterInsert(function ($row) {
            $orderid = date("Ymdhis") . sprintf("%08d", $row['user_id']) . mt_rand(1000, 9999);
            $data = [
                'orderid'   => $orderid,
                'user_id'   => $row['user_id'],
                'vip_id'    => $row['vip_id'],
                'record_id' => $row['id'],
                'title'     => '购买VIP',
                'amount'    => $row['amount'],
                'method'    => 'web',
                'payamount' => 0,
                'paytype'   => 'system',
                'ip'        => request()->ip(),
                'useragent' => substr(request()->server('HTTP_USER_AGENT'), 0, 255),
                'status'    => 'created'
            ];
            $order = \addons\vip\model\Order::create($data);
            if ($row['status'] == 'active') {
                Db::name("vip_record")->where('id', $row['id'])->update(['status' => 'created']);
                \addons\vip\library\Order::settle($orderid, $row['amount']);
            }

        });

        self::beforeDelete(function ($row) {
            if ($row['status'] == 'active') {
                throw new Exception("请先设定为过期再进行删除");
            }
        });

        self::beforeUpdate(function ($row) {
            $changedData = $row->getChangedData();
            $originData = $row->getOriginData();
            if (isset($changedData['status'])) {
                if ($changedData['status'] == 'active') {
                    $userVipInfo = Service::getVipInfo($row['user_id']);
                    if ($userVipInfo && $userVipInfo['level'] > $row['level']) {
                        throw new Exception("用户当前VIP等级过高，无法修改");
                    }
                }
            }
        });
        self::afterUpdate(function ($row) {
            $changedData = $row->getChangedData();
            $originData = $row->getOriginData();
            if (isset($changedData['status'])) {
                $order = Order::where('user_id', $row['user_id'])->where('record_id', $row['id'])->find();
                if ($order) {
                    if ($order->amount != $row['amount']) {
                        $order->save(['amount' => $row['amount']]);
                    }
                    if ($changedData['status'] == 'active') {
                        Db::name("vip_record")->where('id', $row['id'])->update(['status' => $originData['status']]);
                        \addons\vip\library\Order::settle($order->orderid, $row['amount']);
                    } elseif ($originData['status'] == 'active') {
                        Db::name("vip_record")->where('id', $row['id'])->update(['status' => $originData['status']]);
                        \addons\vip\library\Order::unsettle($order->orderid, $changedData['status']);
                    }
                } else {
                    throw new Exception("未找到关联订单");
                }
            }
        });

    }

    public function getStatusList()
    {
        return ['created' => __('Status created'), 'active' => __('Status active'), 'expired' => __('Status expired'), 'finished' => __('Status finished'), 'locked' => __('Status locked'), 'canceled' => __('Status canceled')];
    }


    public function getExpiretimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['expiretime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }

    protected function setExpiretimeAttr($value)
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

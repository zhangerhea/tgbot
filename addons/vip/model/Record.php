<?php

namespace addons\vip\model;

use app\common\library\Auth;
use app\common\model\User;
use think\Db;
use think\Hook;
use think\Model;

class Record extends Model
{

    // 表名
    protected $name = 'vip_record';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
    ];

    public function getStatusTextAttr($value, $data)
    {
        $statusArr = [
            'created'  => '待支付',
            'active'   => '生效中',
            'expired'  => '已到期',
            'locked'   => '锁定中',
            'upgraded' => '已升级',
            'finished' => '已结束',
            'canceled' => '已取消'
        ];
        return isset($statusArr[$data['status']]) ? $statusArr[$data['status']] : '未知';
    }

    /**
     * 设定VIP已生效
     * @param int $id VIP记录ID
     * @return bool
     */
    public static function settle($id)
    {
        $recordInfo = self::get($id);
        if (!$recordInfo) {
            return false;
        }
        if ($recordInfo['status'] == 'active') {
            return true;
        }

        //设定过期时间和状态
        $expiretime = time() + $recordInfo['days'] * 86400;

        $lastRecordInfo = self::getLastRecord($recordInfo['user_id']);
        if ($lastRecordInfo) {
            if ($lastRecordInfo['level'] == $recordInfo['level']) {
                //等级相同，直接设置为已完成
                $lastRecordInfo->save(['status' => 'finished']);
                $expiretime = $lastRecordInfo->expiretime + $recordInfo['days'] * 86400;
            } else {
                //等级较低，统一处理
            }
        }

        //变更低等级的过期时间和状态
        Record::where('user_id', $recordInfo['user_id'])
            ->where('level', '<', $recordInfo['level'])
            ->where('status', 'in', ['locked', 'active'])
            ->update(['expiretime' => Db::raw('expiretime+' . ($recordInfo['days'] * 86400)), 'status' => 'locked']);

        $recordInfo->save(['status' => 'active', 'expiretime' => $expiretime]);

        //变更会员VIP等级
        $userInfo = User::get($recordInfo['user_id']);
        if ($userInfo && isset($userInfo['vip'])) {
            $userInfo->save(['vip' => $recordInfo['level']]);
        }
        Hook::listen('vip_record_begin', $recordInfo);
    }

    /**
     * 设定VIP未生效
     * @param int $id VIP记录ID
     * @param string $status 设定状态
     * @return bool
     */
    public static function unsettle($id, $status = 'expired')
    {
        $recordInfo = self::get($id, ['user']);
        if (!$recordInfo) {
            return false;
        }
        if ($recordInfo->status == $status) {
            return true;
        }
        $recordInfo->save(['status' => $status]);
        //匹配锁定未过期的数据
        $lockRecordList = Record::where('status', 'locked')
            ->where('user_id', $recordInfo->user_id)
            ->where('id', '<>', $recordInfo->id)
            ->order('level', 'desc')
            ->select();
        if (count($lockRecordList) > 0) {
            $time = time();
            foreach ($lockRecordList as $subindex => $subitem) {
                if ($subitem['expiretime'] > $time) {
                    $subitem->save(['status' => 'active']);
                    $subitem->user && $subitem->user->save(['vip' => $subitem['level']]);
                    Hook::listen('vip_record_begin', $subitem);
                    break;
                } else {
                    $subitem->save(['status' => 'expired']);
                    $subitem->user && $subitem->user->save(['vip' => 0]);
                    Hook::listen('vip_record_end', $recordInfo);
                }
            }
        } else {
            $recordInfo->user && $recordInfo->user->save(['vip' => 0]);
            Hook::listen('vip_record_end', $recordInfo);
        }
        return true;
    }

    /**
     * 获取最后一条生效中的VIP记录
     * @param int $user_id 会员ID
     * @return array|false|\PDOStatement|string|Model
     */
    public static function getLastRecord($user_id = null)
    {
        $user_id = is_null($user_id) ? Auth::instance()->id : $user_id;
        $lastRecordInfo = Record::where('user_id', $user_id)->where('status', 'active')->order('level', 'desc')->find();
        if ($lastRecordInfo && $lastRecordInfo['expiretime'] < time()) {
            Record::unsettle($lastRecordInfo['id'], 'expired');
            $lastRecordInfo = self::getLastRecord($user_id);
        }
        return $lastRecordInfo;
    }

    public function user()
    {
        return $this->belongsTo('\app\common\model\User', "user_id", "id", [], 'LEFT');
    }

    public function vip()
    {
        return $this->belongsTo('Vip', "vip_id", "id", [], 'LEFT');
    }

}

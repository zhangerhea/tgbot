<?php

namespace addons\vip\library;

use addons\vip\model\Record;
use addons\vip\model\Vip;
use app\common\library\Auth;
use think\Db;
use think\Log;

class Service
{
    /**
     * 检测是否满足VIP等级
     * @param int  $level   VIP等级
     * @param bool $strict  是否严格模式，严格模式则VIP等级必须相等
     * @param int  $user_id 会员ID
     * @return bool
     */
    public static function isVip($level = 1, $strict = false, $user_id = null)
    {
        static $vipArr = [];
        $tag = 'vip-' . $user_id . '-' . $level;
        if (isset($vipArr[$tag])) {
            $recordLevel = $vipArr[$tag];
        } else {
            $user_id = is_null($user_id) ? Auth::instance()->id : $user_id;
            $recordInfo = Record::getLastRecord($user_id);
            $recordLevel = $recordInfo ? $recordInfo['level'] : 0;
            $vipArr[$tag] = $recordLevel;
        }
        if ((!$strict && $recordLevel >= $level) || ($strict && $recordLevel == $level)) {
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * 获取VIP信息
     * @param int $user_id 会员ID
     * @return array
     */
    public static function getVipInfo($user_id = null)
    {
        $user_id = is_null($user_id) ? Auth::instance()->id : $user_id;
        if (!$user_id) {
            return [];
        }
        $recordInfo = Record::getLastRecord($user_id);
        $result = [];
        if ($recordInfo) {
            $vip = $recordInfo->vip;
            $result = [
                'id'            => $vip ? $vip['id'] : 0,
                'name'          => $vip ? $vip['name'] : '未知',
                'level'         => $recordInfo['level'],
                'expiredate'    => datetime($recordInfo['expiretime']),
                'remainseconds' => max(0, $recordInfo['expiretime'] - time()),
                'image'         => $vip ? $vip['image'] : '',
                'rightdata'     => $vip ? $vip['rightdata'] : []
            ];
        }
        return $result;
    }

    /**
     * 获取VIP列表
     * @return array
     */
    public static function getVipList()
    {
        $vipList = Vip::where('status', 'normal')->field('level,name,image,label,intro')->order('level', 'asc')->select();
        $vipList = $vipList ? collection($vipList)->toArray() : [];
        return $vipList;
    }

    /**
     * 判断VIP是否过期
     * @param mixed $user_id 会员ID
     */
    public static function checkVipExpired($user_id = null)
    {
        //匹配用户所有过期的VIP数据
        $user_id = is_null($user_id) ? Auth::instance()->id : $user_id;
        $recordList = Db::name('vip_record')->where('status', 'active')
            ->where(function ($query) use ($user_id) {
                //为-1时表示全部，用于定时任务
                if (is_numeric($user_id) && $user_id > -1) {
                    $query->where('user_id', $user_id);
                }
            })
            ->where('expiretime', '<', time())
            ->order('level', 'desc')
            ->select();

        foreach ($recordList as $index => $item) {
            Db::startTrans();
            try {
                Record::unsettle($item['id'], 'expired');
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                Log::record("Vip error:" . $e->getMessage());
                continue;
            }
        }
    }
}

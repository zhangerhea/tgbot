<?php

namespace addons\leesign\controller;

use think\addons\Controller;
use fast\Date;

/**
 * 每日签到
 */
class Index extends Controller
{

    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('addons\leesign\model\Leesign');
    }

    // 签到页
    public function index()
    {

        //签到配置
        $addonCfg = get_addon_config('leesign');

        $rule = nl2br($addonCfg['rule']);
        
        //当月的第一天
        $firstDate = date("Y-m-d H:i:s", Date::unixtime("month", 0, 'begin'));
        //当月的最后一天
        $lastDate = date('Y-m-d H:i:s', Date::unixtime("month", 0, 'end'));

        $where['sign_time'] = ['between', [$firstDate, $lastDate]];
        $where['uid'] = $this->auth->id;
        $signlist = $this->model->where($where)->order('sign_time desc')->select();
        $days = [];
        foreach ($signlist as $k => $v)
        {

            if (date("Y-m") == substr($v['sign_time'], 0, 7))
            {
                $days[] = (int) substr($v['sign_time'], 8, 2);
            }
        }
        $mylist = $this->getMySign();
        $this->assign('mylist', $mylist);
        $this->assign('days', $days);
        $this->assign('rule', $rule);
        return $this->fetch();
    }

    // 获取签到面板所需信息
    public function getSignInfo()
    {
        if (!$this->auth->isLogin())
        {
            $this->result([], -1, __('Please login first'));
        }

        //当月的第一天
        $firstDate = date("Y-m-d H:i:s", Date::unixtime("month", 0, 'begin'));
        //当月的最后一天
        $lastDate = date('Y-m-d H:i:s', Date::unixtime("month", 0, 'end'));

        $w['sign_time'] = ['between', [$firstDate, $lastDate]];

        $w['uid'] = $this->auth->id;

        //本月签到数
        $len = $this->model->where($w)->order('sign_time desc')->count();

        //额外获得积分数
        $extra_total = $this->model->where($w)->sum('sign_extra_reward');
        $extra_total = $extra_total ? $extra_total : 0;

        //连续签到数
        //连续签到数
        $row = $this->model->where($w)->order('sign_time desc')->field('max_sign')->find();
        $lianxu = $row ? $row['max_sign'] : 0;

        $signlist = $this->model->where($w)->order('sign_time desc')->field('sign_time,sign_reward,sign_extra_reward')->select();

        $data = $signlist ? $signlist : __('sign empty');

        $this->result(['length' => $len, 'extra_total' => $extra_total, 'lianxu' => $lianxu, 'data' => $data], 1, __('sign reward'));
    }

    //获取我本月已签到记录
    protected function getMySign (){
        //当月的第一天
        $firstDate = date("Y-m-d H:i:s", Date::unixtime("month", 0, 'begin'));
        //当月的最后一天
        $lastDate = date('Y-m-d H:i:s', Date::unixtime("month", 0, 'end'));

        $w['uid'] = $this->auth->id;
        $w['sign_time'] = ['between', [$firstDate, $lastDate]];
        $signlist = $this->model->where($w)->order('sign_time desc')->select();
        $arr = [];

        foreach($signlist as $k => $v){
            $arr[] = (int) substr($v['sign_time'], 8,2);
        }
        $arr = implode(",", $arr);
        
        $str = "{".$arr."}";

        return $str;
        
    }

    //签到逻辑
    public function sign()
    {
        if (!$this->auth->isLogin())
        {
            $this->result([], -1, __('Please login first'));
        }

        $curr = date('Y-m-d', time());
        $ww['uid'] = $this->auth->id;
        $repeat = $this->model->where("DATE_FORMAT(sign_time,'%Y-%m-%d') = '$curr'")->where($ww)->find();
        if ($repeat)
        {
            $this->result([], 0, __('repeat sign'));
        }

        //签到配置
        $config = get_addon_config('leesign');

        //当月的第一天
        $firstDate = date("Y-m-d H:i:s", Date::unixtime("month", 0, 'begin'));
        //当月的最后一天
        $lastDate = date('Y-m-d H:i:s', Date::unixtime("month", 0, 'end'));

        $w['sign_time'] = ['between', [$firstDate, $lastDate]];
        $w['uid'] = $this->auth->id;

        $signList = $this->model->where($w)->order('sign_time desc')->select();
        $len = count($signList, 0);

        if ($signList && $len >= 1)
        {
            $lianxu = (date("Y-m-d", strtotime($signList[0]['sign_time'] . "+ 1 day")) != date("Y-m-d", time())) ? false : true;
        }
        else
        {
            $lianxu = false;
        }

        //处理逻辑：如果上次签到的日期和这次签到的日期相差不是1天，那么本次签到就不是连续签到。
        $max_sign_num = $lianxu ? $signList[0]['max_sign'] + 1 : 1;

        $score = $config['signnum'];

        //连续签到奖励规则 - 周期奖励
        $zhouqi = $config['types'];

        //当月连续签到所获得的所有额外奖励
        $extra = 0;

        //当天是否触发连续签到的额外奖励
        $extra_reward = 0;

        //开启了连续签到奖励
        if ($config['signstatus'] == 1)
        {
            //计算连续签到带来的额外奖励
            foreach ($zhouqi as $k => $v)
            {
                foreach ($signList as $key => $val)
                {
                    if ($k == $val['max_sign'])
                    {
                        $extra += $v;
                        break;
                    }
                }

                if ($k == $max_sign_num)
                {
                    $extra_reward += $v;
                }
            }
        }

        $data = [
            'sign_ip'           => $this->request->ip(),
            'uid'               => $this->auth->id,
            'sign_time'         => date('Y-m-d H:i:s'),
            'sign_reward'       => $score,
            'sign_extra_reward' => $extra_reward,
            'max_sign'          => $max_sign_num
        ];

        if ($this->model->insert($data))
        {
            //签到积分增加日志
            \app\common\model\User::score($score, $this->auth->id, '连续签到奖励');

            if ($extra_reward > 0)
            {
                //额外获得积分记录
                \app\common\model\User::score($extra_reward, $this->auth->id, '额外签到奖励');
            }
            $this->result(['max_sign' => $max_sign_num, 'month_sign_num' => $len, 'reward' => ($score + $extra_reward)], 1, __('sign success tip', $max_sign_num));
        }
        else
        {
            $this->result([], 0, __('sign failed'));
        }
    }

}

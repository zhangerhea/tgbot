<?php

namespace addons\vip\controller\api;

use addons\vip\library\OrderException;
use addons\third\model\Third;
use addons\vip\library\Service;
use addons\vip\model\Record;
use addons\vip\model\Vip;
use fast\Date;
use think\Db;
use think\Exception;
use think\exception\PDOException;

class Order extends Base
{

    protected $noNeedLogin = ['epay'];

    /**
     * 创建订单并发起支付请求
     */
    public function submit()
    {
        $level = $this->request->param('level/d');
        $days = $this->request->param('days/d');
        $paytype = $this->request->param('paytype', '');
        $method = $this->request->param('method');
        $appid = $this->request->param('appid');//APP的应用ID
        $openid = $this->request->param('openid', '');//Openid 用于小程序和公众号登录场景
        $returnurl = $this->request->param('returnurl', '', 'trim');

        $vipInfo = Vip::getByLevel($level);
        if (!$vipInfo) {
            $this->error('未找到VIP相关信息');
        }
        if ($this->auth->vip > $vipInfo['level']) {
            $this->error('当前VIP等级已高于购买的VIP等级');
        }
        if (!in_array($paytype, ['alipay', 'wechat', 'balance'])) {
            $this->error('支付方式错误');
        }
        $amount = $vipInfo->getPriceByDays($days);
        $insert = [
            'user_id' => $this->auth->id,
            'vip_id'  => $vipInfo->id,
            'level'   => $vipInfo->level,
            'days'    => $days,
            'amount'  => $amount,
            'status'  => 'created',
        ];
        $vipRecord = Record::create($insert, true);

        //公众号和小程序
        if ($paytype!=='balance' && !$openid && in_array($method, ['miniapp', 'mp'])) {
            $info = get_addon_info('third');
            if (!$info || !$info['state']) {
                $this->error("请在后台安装第三方登录插件");
            }
            $third = Third::where('platform', 'wechat')->where('apptype', $method)->where('user_id', $this->auth->id)->find();
            if (!$third) {
                $this->error("未找到登录用户信息", 'bind');
            }
            $openid = $third['openid'];
        }

        try {
            $response = \addons\vip\library\Order::submit($vipInfo->id, $vipRecord->id, $amount, $paytype, $method, $openid, '', $returnurl);
        } catch (OrderException $e) {
            if ($e->getCode() == 1) {
                $this->success($e->getMessage());
            } else {
                $this->error($e->getMessage());
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success(__(''), $response);
    }

}

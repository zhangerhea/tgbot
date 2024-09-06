<?php

namespace addons\recharge\library;

use app\common\library\Auth;
use app\common\model\User;
use think\Exception;

class Order
{

    /**
     * 发起订单支付
     *
     * @param float  $money     金额
     * @param string $paytype   支付类型
     * @param string $method    支付方法
     * @param string $openid    Openid
     * @param string $notifyurl 通知地址
     * @param string $returnurl 返回地址
     * @return \addons\epay\library\Collection|\addons\epay\library\RedirectResponse|\addons\epay\library\Response
     * @throws Exception
     */
    public static function submit($money, $paytype = 'wechat', $method = 'web', $openid = '', $notifyurl = '', $returnurl = '')
    {
        $auth = Auth::instance();
        $user_id = $auth->isLogin() ? $auth->id : 0;
        $order = null;
        $config = get_addon_config('recharge');
        if ($config && $config['ordercreatelimit']) {
            $order = \addons\recharge\model\Order::where('user_id', $user_id)
                ->where('amount', $money)
                ->where('status', 'created')
                ->order('id', 'desc')
                ->find();
        }
        $request = \think\Request::instance();
        if (!$order) {
            $orderid = date("Ymdhis") . sprintf("%08d", $user_id) . mt_rand(1000, 9999);
            $data = [
                'orderid'   => $orderid,
                'user_id'   => $user_id,
                'amount'    => $money,
                'payamount' => 0,
                'paytype'   => $paytype,
                'ip'        => $request->ip(),
                'useragent' => substr($request->server('HTTP_USER_AGENT'), 0, 255),
                'status'    => 'created'
            ];
            $order = \addons\recharge\model\Order::create($data);
        }

        $epay = get_addon_info('epay');

        if (empty($epay) || !$epay['state']) {
            $result = \think\Hook::listen('recharge_order_submit', $order);
            if (!$result) {
                throw new Exception("请先在后台安装并配置微信支付宝整合插件");
            }
        }

        $notifyurl = $notifyurl ? $notifyurl : $request->root(true) . '/index/recharge/epay/type/notify/paytype/' . $paytype;
        $returnurl = $returnurl ? $returnurl : $request->root(true) . '/index/recharge/epay/type/return/paytype/' . $paytype;

        $params = [
            'amount'    => $money,
            'orderid'   => $order->orderid,
            'type'      => $paytype,
            'title'     => "充值{$money}元",
            'notifyurl' => $notifyurl,
            'returnurl' => $returnurl,
            'method'    => $method,
            'openid'    => $openid
        ];

        //微信小程序或公众号支付
        if (!$openid && in_array($method, ['mp', 'miniapp'])) {
            throw new Exception("公众号和小程序支付openid不能为空！");
        }

        $response = \addons\epay\library\Service::submitOrder($params);
        return $response;
    }


    /**
     * 订单结算
     * @param int    $orderid   订单号
     * @param float  $payamount 支付金额
     * @param string $memo      备注
     * @return bool
     */
    public static function settle($orderid, $payamount, $memo = '')
    {
        $order = \addons\recharge\model\Order::getByOrderid($orderid);
        if (!$order) {
            return false;
        }
        if ($payamount != $order['amount']) {
            \think\Log::write("[recharge][pay][{$orderid}][订单支付金额不一致]");
            return false;
        }
        if ($order['status'] != 'paid') {
            $order->payamount = $payamount;
            $order->paytime = time();
            $order->status = 'paid';
            $order->memo = $memo;
            $order->save();

            // 更新会员余额
            User::money($payamount, $order->user_id, '充值');

            $result = \think\Hook::listen('recharge_order_settled', $order);
        }
        return true;
    }
}

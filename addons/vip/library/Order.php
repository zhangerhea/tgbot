<?php

namespace addons\vip\library;

use addons\vip\library\OrderException;
use addons\vip\model\Record;
use app\common\library\Auth;
use app\common\model\User;
use think\Db;
use think\Exception;

class Order
{

    /**
     * 发起订单支付
     *
     * @param int    $vip_id    VIP ID
     * @param int    $record_id 记录ID
     * @param float  $amount    金额
     * @param string $paytype   支付类型
     * @param string $method    支付方法
     * @param string $openid    Openid
     * @param string $notifyurl 通知地址
     * @param string $returnurl 返回地址
     * @return \addons\epay\library\Collection|\addons\epay\library\RedirectResponse|\addons\epay\library\Response
     * @throws Exception
     */
    public static function submit($vip_id, $record_id, $amount, $paytype = 'wechat', $method = 'web', $openid = '', $notifyurl = '', $returnurl = '')
    {
        $auth = Auth::instance();
        $user_id = $auth->isLogin() ? $auth->id : 0;
        $title = "购买VIP";
        $order = null;
        $config = get_addon_config('vip');
        if ($config && $config['ordercreatelimit']) {
            $order = \addons\vip\model\Order::where('user_id', $user_id)
                ->where('vip_id', $vip_id)
                ->where('record_id', $record_id)
                ->where('amount', $amount)
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
                'vip_id'    => $vip_id,
                'record_id' => $record_id,
                'title'     => $title,
                'amount'    => $amount,
                'method'    => $method,
                'payamount' => 0,
                'paytype'   => $paytype,
                'ip'        => $request->ip(),
                'useragent' => substr($request->server('HTTP_USER_AGENT'), 0, 255),
                'status'    => 'created'
            ];
            $order = \addons\vip\model\Order::create($data);
        } else {

            //支付方式变更
            if (($order['method'] && $order['paytype'] == $paytype && $order['method'] != $method)) {
                $orderid = date("Ymdhis") . sprintf("%08d", $user_id) . mt_rand(1000, 9999);
                $order->save(['orderid' => $orderid]);
            }

            //更新支付类型和方法
            $order->save(['paytype' => $paytype, 'method' => $method]);
        }

        //使用余额支付
        if ($paytype == 'balance') {
            if (!$auth->id) {
                throw new OrderException('需要登录后才能够支付');
            }
            if ($auth->money < $amount) {
                throw new OrderException('余额不足，无法进行支付');
            }
            Db::startTrans();
            try {
                User::money(-$amount, $auth->id, $title);
                self::settle($order->orderid, $amount);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                throw new OrderException($e->getMessage());
            }
            throw new OrderException('余额支付成功', 1);
        }

        $epay = get_addon_info('epay');

        if (empty($epay) || !$epay['state']) {
            $result = \think\Hook::listen('vip_order_submit', $order);
            if (!$result) {
                throw new Exception("请先在后台安装并配置微信支付宝整合插件");
            }
        }

        $notifyurl = $notifyurl ?: $request->root(true) . '/index/vip/epay/type/notify/paytype/' . $paytype;
        $returnurl = $returnurl ?: $request->root(true) . '/index/vip/epay/type/return/paytype/' . $paytype;

        //保证取出的金额一致，不一致将导致订单重复错误
        $amount = sprintf("%.2f", $order->amount);

        $params = [
            'amount'    => $amount,
            'orderid'   => $order->orderid,
            'type'      => $paytype,
            'title'     => $title,
            'notifyurl' => $notifyurl,
            'returnurl' => $returnurl,
            'method'    => $method,
            'openid'    => $openid
        ];

        //微信小程序或公众号支付
        if (!$openid && in_array($method, ['mp', 'miniapp'])) {
            throw new OrderException("公众号和小程序支付openid不能为空！");
        }

        $response = \addons\epay\library\Service::submitOrder($params);
        return $response;
    }


    /**
     * 订单结算
     *
     * @param int    $orderid   订单号
     * @param string $payamount 金额
     * @param string $memo      备注
     * @return bool
     */
    public static function settle($orderid, $payamount, $memo = '')
    {
        $order = \addons\vip\model\Order::getByOrderid($orderid);
        if (!$order) {
            return false;
        }
        if ($order['status'] != 'paid') {
            if ($payamount != $order->amount) {
                \think\Log::write("[vip][pay][{$orderid}][订单支付金额不一致]");
                return false;
            }
            try {
                Db::startTrans();
                $order->payamount = $payamount;
                $order->paytime = time();
                $order->status = 'paid';
                $order->memo = $memo;
                $order->save();

                if ($order->payamount == $order->amount) {
                    // 更新会员VIP信息
                    Record::settle($order->record_id);

                    $result = \think\Hook::listen('vip_order_settled', $order);
                }
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return false;
            }
        }
        return true;
    }

    /**
     * 订单反结算(退单)
     *
     * @param string $orderid 订单号
     * @param string $status 状态
     * @return bool
     */
    public static function unsettle($orderid, $status = 'canceled')
    {
        $order = \addons\vip\model\Order::getByOrderid($orderid);
        if (!$order) {
            return false;
        }
        if ($order['status'] == 'created') {
            return true;
        }
        try {
            Db::startTrans();
            $order->payamount = 0;
            $order->paytime = null;
            $order->status = 'created';
            $order->save();

            // 更新会员VIP信息
            Record::unsettle($order->record_id, $status);

            $result = \think\Hook::listen('vip_order_unsettled', $order);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }
        return true;
    }
}

<?php

namespace addons\epay\controller;

use addons\epay\library\Service;
use fast\Random;
use think\addons\Controller;
use Exception;

/**
 * 微信支付宝整合插件首页
 *
 * 此控制器仅用于开发展示说明和测试，请自行添加一个新的控制器进行处理返回和回调事件，同时删除此控制器文件
 *
 * Class Index
 * @package addons\epay\controller
 */
class Index extends Controller
{
    protected $layout = 'default';

    protected $config = [];

    public function _initialize()
    {
        parent::_initialize();
        if (!config("app_debug")) {
            $this->error("仅在开发环境下查看");
        }
    }

    public function index()
    {
        $this->view->assign("title", "微信支付宝整合");
        return $this->view->fetch();
    }

    /**
     * 体验，仅供开发测试
     */
    public function experience()
    {
        $amount = $this->request->post('amount');
        $type = $this->request->post('type');
        $method = $this->request->post('method');
        $openid = $this->request->post('openid', "");

        if (!$amount || $amount < 0) {
            $this->error("支付金额必须大于0");
        }

        if (!$type || !in_array($type, ['alipay', 'wechat'])) {
            $this->error("支付类型不能为空");
        }

        if (in_array($method, ['miniapp', 'mp']) && !$openid) {
            $this->error("openid不能为空");
        }

        //订单号
        $out_trade_no = date("YmdHis") . mt_rand(100000, 999999);

        //订单标题
        $title = '测试订单';

        //回调链接
        $notifyurl = $this->request->root(true) . '/addons/epay/index/notifyx/paytype/' . $type;
        $returnurl = $this->request->root(true) . '/addons/epay/index/returnx/paytype/' . $type . '/out_trade_no/' . $out_trade_no;

        $response = Service::submitOrder($amount, $out_trade_no, $type, $title, $notifyurl, $returnurl, $method, $openid);

        return $response;
    }

    /**
     * 支付成功，仅供开发测试
     */
    public function notifyx()
    {
        $paytype = $this->request->param('paytype');
        $pay = Service::checkNotify($paytype);
        if (!$pay) {
            return json(['code' => 'FAIL', 'message' => '失败'], 500, ['Content-Type' => 'application/json']);
        }

        // 获取回调数据，V3和V2的回调接收不同
        $data = Service::isVersionV3() ? $pay->callback() : $pay->verify();

        try {
            //微信支付V3返回和V2不同
            if (Service::isVersionV3() && $paytype === 'wechat') {
                $data = $data['resource']['ciphertext'];
                $data['total_fee'] = $data['amount']['total'];
            }

            \think\Log::record($data);
            //获取支付金额、订单号
            $payamount = $paytype == 'alipay' ? $data['total_amount'] : $data['total_fee'] / 100;
            $out_trade_no = $data['out_trade_no'];

            \think\Log::record("回调成功，订单号：{$out_trade_no}，金额：{$payamount}");

            //你可以在此编写订单逻辑
        } catch (Exception $e) {
            \think\Log::record("回调逻辑处理错误:" . $e->getMessage(), "error");
        }

        //下面这句必须要执行,且在此之前不能有任何输出
        if (Service::isVersionV3()) {
            return $pay->success()->getBody()->getContents();
        } else {
            return $pay->success()->send();
        }
    }

    /**
     * 支付返回，仅供开发测试
     */
    public function returnx()
    {
        $paytype = $this->request->param('paytype');
        $out_trade_no = $this->request->param('out_trade_no');
        $pay = Service::checkReturn($paytype);
        if (!$pay) {
            $this->error('签名错误', '');
        }

        //你可以在这里定义你的提示信息,但切记不可在此编写逻辑
        $this->success("请返回网站查看支付结果", addon_url("epay/index/index"));
    }
}

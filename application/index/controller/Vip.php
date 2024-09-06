<?php

namespace app\index\controller;

use addons\vip\library\OrderException;
use addons\vip\library\Service;
use addons\vip\model\Order;
use addons\vip\model\Record;
use app\common\controller\Frontend;
use think\Exception;

/**
 * 购买VIP
 */
class Vip extends Frontend
{
    protected $layout = 'default';
    protected $noNeedLogin = ['pay', 'epay'];
    protected $noNeedRight = ['*'];

    /**
     * VIP列表
     */
    public function viplist()
    {
        $config = get_addon_config('vip');
        $vipList = [];
        $vipList = \addons\vip\model\Vip::where('status', '=', 'normal')->field('sales', true)->order('level', 'asc')->select();

        $paytypeListArr = array_filter(explode(',', $config['paytypelist']));
        $defaultPaytype = $config['defaultpaytype'];
        $defaultPaytype = in_array($defaultPaytype, $paytypeListArr) ? $defaultPaytype : reset($paytypeListArr);

        $paytypeList = [];
        foreach ($paytypeListArr as $index => $item) {
            $paytypeList[] = ['value' => $item, 'image' => '/assets/addons/vip/img/' . $item . '.png', 'default' => $item === $defaultPaytype];
        }
        $vipInfo = Service::getVipInfo();

        $this->view->assign('addonConfig', $config);
        $this->view->assign('vipList', $vipList);
        $this->view->assign('vipInfo', $vipInfo);
        $this->view->assign('paytypeList', $paytypeList);
        $this->view->assign('title', __('VIP列表'));
        return $this->view->fetch();
    }

    /**
     * VIP记录
     */
    public function record()
    {
        $recordList = Record::with(['vip'])->where('user_id', $this->auth->id)
            ->where('status', '<>', 'created')
            ->order('id', 'desc')
            ->paginate();

        $vipInfo = Service::getVipInfo();
        $this->view->assign('title', "VIP记录");
        $this->view->assign('recordList', $recordList);
        $this->view->assign('vipInfo', $vipInfo);
        return $this->view->fetch();
    }

    /**
     * 创建订单并发起支付请求
     */
    public function submit()
    {
        $level = $this->request->param('level/d');
        $days = $this->request->param('days/d');
        $paytype = $this->request->param('paytype', '');

        $vipInfo = \addons\vip\model\Vip::getByLevel($level);
        if (!$vipInfo) {
            $this->error('未找到VIP相关信息');
        }
        if ($this->auth->vip > $vipInfo['level']) {
            $this->error('当前VIP等级已高于购买的VIP等级');
        }

        $lastRecordInfo = Record::getLastRecord();

        $recordInfo = Record::where('user_id', $this->auth->id)
            ->where('status', 'created')
            ->where('level', $level)->where('days', $days)
            ->whereTime('createtime', '-30 minutes')
            ->find();
        if (!$recordInfo) {
            $amount = $vipInfo->getPriceByDays($days);
            $insert = [
                'user_id' => $this->auth->id,
                'vip_id'  => $vipInfo->id,
                'level'   => $vipInfo->level,
                'days'    => $days,
                'amount'  => $amount,
                'status'  => 'created',
            ];
            $recordInfo = Record::create($insert);
        }

        try {
            $response = \addons\vip\library\Order::submit($vipInfo->id, $recordInfo->id, $recordInfo->amount, $paytype);
        } catch (OrderException $e) {
            if ($e->getCode() == 1) {
                $this->success($e->getMessage(), "index/vip/record");
            } else {
                $this->error($e->getMessage());
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        return $response;
    }

    /**
     * 企业支付通知和回调
     */
    public function epay()
    {
        $type = $this->request->param('type');
        $paytype = $this->request->param('paytype');
        if ($type == 'notify') {
            $pay = \addons\epay\library\Service::checkNotify($paytype);
            if (!$pay) {
                echo '签名错误';
                return;
            }
            //判断是V2还是V3
            $data = \addons\epay\library\Service::isVersionV3() ? $pay->callback() : $pay->verify();
            try {
                //微信支付V3返回和V2不同
                if (\addons\epay\library\Service::isVersionV3() && $paytype === 'wechat') {
                    $data = $data['resource']['ciphertext'];
                    $data['total_fee'] = $data['amount']['total'];
                }

                $payamount = $paytype == 'alipay' ? $data['total_amount'] : $data['total_fee'] / 100;
                \addons\vip\library\Order::settle($data['out_trade_no'], $payamount);
            } catch (Exception $e) {
                \think\Log::record("回调逻辑处理错误:" . $e->getMessage(), "error");
            }

            //下面这句必须要执行,且在此之前不能有任何输出
            if (\addons\epay\library\Service::isVersionV3()) {
                return $pay->success()->getBody()->getContents();
            } else {
                return $pay->success()->send();
            }

        } else {
            $this->success("请返回网站查看支付状态!", "index/vip/viplist");
        }
    }
}

<?php

namespace addons\recharge\controller\api;

use addons\epay\library\Collection;
use addons\third\model\Third;

class Recharge extends Base
{

    protected $noNeedLogin = ['config'];
    protected $noNeedRight = ['*'];

    public function config()
    {
        $this->success(__(''), $this->config);
    }

    /**
     * 创建订单并发起支付请求
     */
    public function submit()
    {
        $info = get_addon_info('epay');
        if (!$info || !$info['state']) {
            $this->error('请在后台插件管理安装微信支付宝整合插件后重试');
        }
        $money = $this->request->param('money/f');
        $paytype = $this->request->param('paytype', '');
        $method = $this->request->param('method');
        $appid = $this->request->param('appid');//APP的应用ID
        $returnurl = $this->request->param('returnurl', '', 'trim');
        $openid = $this->request->param('openid', '', 'trim');

        if ($money <= 0) {
            $this->error('充值金额不正确');
        }
        if (isset($this->config['minmoney']) && $money < $this->config['minmoney']) {
            $this->error('充值金额不能低于' . $this->config['minmoney'] . '元');
        }

        //微信小程序或公众号支付
        if (!$openid && in_array($method, ['miniapp', 'mp'])) {
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
            $response = \addons\recharge\library\Order::submit($money, $paytype, $method, $openid, '', $returnurl);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success(__(''), $response);
    }

}

<?php

namespace addons\leescore\controller;

use addons\epay\library\Service;
use think\addons\Controller;
use think\Db;

/**
 * 积分商城支付回调处理: 已废弃
 * By:龙组的赵日天
 * Time: 2018-12-14
 * Version: v1.1.0
 */

/**
 * API接口控制器
 *
 * @package addons\leescore\controller
 */
class Api extends Controller
{

    protected $config = [];
    protected $order = null;
    protected $cart = null;
    protected $noNeedLogin = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->order = model('addons\leescore\model\Order');
        $this->cart = model('addons\leescore\model\Cart');
    }

    /**
     * 默认方法
     */
    public function index()
    {
        $this->error();
    }

}

<?php

namespace addons\vip\controller\api;

use app\common\controller\Api;
use app\common\library\Auth;
use think\Config;
use think\Lang;

class Base extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];
    //设置返回的会员字段
    protected $allowFields = ['id','nickname', 'mobile', 'avatar', 'score', 'vip', 'level', 'bio', 'balance', 'money', 'gender'];

    public function _initialize()
    {

        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header('Access-Control-Expose-Headers: __token__');//跨域让客户端获取到
        }
        //跨域检测
        check_cors_request();

        if (!isset($_COOKIE['PHPSESSID'])) {
            Config::set('session.id', $this->request->server("HTTP_SID"));
        }
        parent::_initialize();
        $config = get_addon_config('vip');

        Config::set('vip', $config);
        Config::set('default_return_type', 'json');
        Auth::instance()->setAllowFields($this->allowFields);

        //这里手动载入语言包
        Lang::load(APP_PATH . '/index/lang/zh-cn/user.php');
    }

}

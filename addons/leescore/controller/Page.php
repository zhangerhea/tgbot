<?php

namespace addons\leescore\controller;

use think\addons\Controller;
use think\Db;
use think\Config;

/**
 * 积分商城单页模板
 * By:龙组的赵日天
 * Time: 2022-4-26
 * Version: v1.1.0
 */
class Page extends Base
{
    protected $model = null;
    protected $layout = 'default';

    public function _initialize()
    {
        parent::_initialize();

    }

    public function Detail()
    {
        $id = input("get.id");
        $detail = Db::name('leescore_link')->find();
        $this->view->assign('detail', $detail);
        return $this->fetch();
    }
}

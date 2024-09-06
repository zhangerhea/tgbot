<?php

namespace addons\vip\controller;

use addons\vip\library\Service;
use addons\vip\model\Record;
use think\Controller;
use think\Db;
use think\Log;

/**
 * VIP定时任务接口
 *
 * 以Crontab方式每分钟定时执行,且只可以Cli方式运行
 * @internal
 */
class Autotask extends Controller
{

    /**
     * 初始化方法,最前且始终执行
     */
    public function _initialize()
    {
        // 只可以以cli方式执行
        if (!$this->request->isCli()) {
            $this->error('Autotask script only work at client!');
        }

        parent::_initialize();

        // 清除错误
        error_reporting(0);

        // 设置永不超时
        set_time_limit(0);
    }

    /**
     * 执行定时任务
     */
    public function index()
    {
        $time = time();
        //清理锁定且过期的数据
        Record::where('status', 'locked')->where('expiretime', '<', $time)->update(['status' => 'expired']);

        //判断用户VIP是否过期，-1表示全部
        Service::checkVipExpired(-1);
        echo "done";
    }

}

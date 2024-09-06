<?php

namespace addons\vip;

use addons\vip\library\Service;
use addons\vip\model\Record;
use app\common\library\Auth;
use app\common\library\Menu;
use think\Addons;
use think\Request;

/**
 * 插件
 */
class Vip extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu = [
            [
                'name'    => 'vip',
                'title'   => 'VIP管理',
                'icon'    => 'fa fa-diamond',
                'sublist' => [
                    [
                        'name'    => 'vip/vip',
                        'title'   => 'VIP等级管理',
                        'icon'    => 'fa fa-diamond',
                        'sublist' => [
                            ['name' => 'vip/vip/index', 'title' => '查看'],
                            ['name' => 'vip/vip/add', 'title' => '添加'],
                            ['name' => 'vip/vip/edit', 'title' => '修改'],
                            ['name' => 'vip/vip/del', 'title' => '删除'],
                            ['name' => 'vip/vip/multi', 'title' => '批量更新'],
                        ]
                    ],
                    [
                        'name'    => 'vip/order',
                        'title'   => 'VIP订单管理',
                        'icon'    => 'fa fa-cny',
                        'sublist' => [
                            ['name' => 'vip/order/index', 'title' => '查看'],
                            ['name' => 'vip/order/add', 'title' => '添加'],
                            ['name' => 'vip/order/edit', 'title' => '修改'],
                            ['name' => 'vip/order/del', 'title' => '删除'],
                            ['name' => 'vip/order/multi', 'title' => '批量更新'],
                        ]
                    ],
                    [
                        'name'    => 'vip/record',
                        'title'   => 'VIP记录管理',
                        'icon'    => 'fa fa-list',
                        'sublist' => [
                            ['name' => 'vip/record/index', 'title' => '查看'],
                            ['name' => 'vip/record/add', 'title' => '添加'],
                            ['name' => 'vip/record/edit', 'title' => '修改'],
                            ['name' => 'vip/record/del', 'title' => '删除'],
                            ['name' => 'vip/record/multi', 'title' => '批量更新'],
                            ['name' => 'vip/record/recheck', 'title' => '判断并刷新VIP会员过期时间'],
                        ]
                    ]
                ]
            ],

        ];
        Menu::create($menu);
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        Menu::delete('vip');
        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {
        Menu::enable('vip');
        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {
        Menu::disable('vip');
        return true;
    }

    /**
     * 会员中心边栏后
     * @return mixed
     * @throws \Exception
     */
    public function userSidenavAfter()
    {
        $request = Request::instance();
        $controllername = strtolower($request->controller());
        $actionname = strtolower($request->action());
        $config = get_addon_config('vip');
        $sidenav = array_filter(explode(',', $config['usersidenav']));
        if (!$sidenav) {
            return '';
        }
        $data = [
            'controllername' => $controllername,
            'actionname'     => $actionname,
            'sidenav'        => $sidenav
        ];

        return $this->fetch('view/hook/user_sidenav_after', $data);
    }

    /**
     * 判断登录用户VIP是否过期
     * @param $upload
     */
    public function uploadConfigInit($upload)
    {
        $vipConfig = get_addon_config('vip');
        if (isset($vipConfig['expirecheckmode'])) {
            $auth = Auth::instance();
            if ($vipConfig['expirecheckmode'] === 'hook' && $auth->isLogin()) {
                Service::checkVipExpired($auth->id);
            }
        }
    }

    /**
     * 用户登录成功时判断一次
     * @param $user
     */
    public function userLoginSuccessed($user)
    {
        //清除锁定且过期的数据
        Record::where('status', 'locked')->where('expiretime', '<', time())->update(['status' => 'expired']);
        //判断用户VIP是否过期
        Service::checkVipExpired($user->id);
    }
}

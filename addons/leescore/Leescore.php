<?php

namespace addons\leescore;

use app\common\library\Menu;
use think\Addons;
use think\Db;
use think\Session;
use think\Config;
use think\Loader;
use think\Exception;
use think\Request;

/**
 * 积分商城
 */
class Leescore extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu = [
            [
                'name'    => 'leescore',
                'title'   => '积分商城',
                'sublist' => [
                    [
                        'name'    => 'leescore/leescorecategory',
                        'title'   => '分类管理',
                        'ismenu'  => 1,
                        'icon'    => 'fa fa-file-text-o',
                        'sublist' => [
                            ['name' => 'leescore/leescorecategory/index', 'title' => '查看'],
                            ['name' => 'leescore/leescorecategory/add', 'title' => '添加'],
                            ['name' => 'leescore/leescorecategory/edit', 'title' => '修改'],
                            ['name' => 'leescore/leescorecategory/del', 'title' => '删除'],
                            ['name' => 'leescore/leescorecategory/multi', 'title' => '批量更新'],
                        ]
                    ],
                    [
                        'name'    => 'leescore/leescoregoods',
                        'title'   => '商品管理',
                        'icon'    => 'fa fa-list',
                        'ismenu'  => 1,
                        'sublist' => [
                            ['name' => 'leescore/leescoregoods/index', 'title' => '查看'],
                            ['name' => 'leescore/leescoregoods/add', 'title' => '添加'],
                            ['name' => 'leescore/leescoregoods/edit', 'title' => '修改'],
                            ['name' => 'leescore/leescoregoods/del', 'title' => '删除'],
                            ['name' => 'leescore/leescoregoods/multi', 'title' => '批量更新'],
                        ]
                    ],
                    [
                        'name'    => 'leescore/leescoreorder',
                        'title'   => '订单管理',
                        'icon'    => 'fa fa-list',
                        'ismenu'  => 1,
                        'sublist' => [
                            ['name' => 'leescore/leescoreorder/index', 'title' => '查看'],
                            ['name' => 'leescore/leescoreorder/del', 'title' => '删除'],
                            ['name' => 'leescore/leescoreorder/send', 'title' => '详情'],
                        ]
                    ],
                    [
                        'name'    => 'leescore/leescoreads',
                        'title'   => '广告位管理',
                        'icon'    => 'fa fa-list',
                        'ismenu'  => 1,
                        'sublist' => [
                            ['name' => 'leescore/leescoreads/index', 'title' => '查看'],
                            ['name' => 'leescore/leescoreads/add', 'title' => '添加'],
                            ['name' => 'leescore/leescoreads/edit', 'title' => '修改'],
                            ['name' => 'leescore/leescoreads/del', 'title' => '删除'],
                            ['name' => 'leescore/leescoreads/multi', 'title' => '批量更新'],
                        ]
                    ],
                    [
                        'name'    => 'leescore/link',
                        'title'   => '碎片管理',
                        'icon'    => 'fa fa-list',
                        'ismenu'  => 1,
                        'sublist' => [
                            ['name' => 'leescore/link/index', 'title' => '查看'],
                            ['name' => 'leescore/link/add', 'title' => '添加'],
                            ['name' => 'leescore/link/edit', 'title' => '修改'],
                            ['name' => 'leescore/link/del', 'title' => '删除'],
                        ]
                    ],
                    [
                        'name'    => 'leescore/linkcategory',
                        'title'   => '碎片分类',
                        'icon'    => 'fa fa-list',
                        'ismenu'  => 1,
                        'sublist' => [
                            ['name' => 'leescore/linkcategory/index', 'title' => '查看'],
                            ['name' => 'leescore/linkcategory/add', 'title' => '添加'],
                            ['name' => 'leescore/linkcategory/edit', 'title' => '修改'],
                            ['name' => 'leescore/linkcategory/del', 'title' => '删除'],
                        ]
                    ],
                ]
            ]
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
        Menu::delete('leescore');
        return true;
    }

    /**
     * 插件升级方法
     */
    public function upgrade(){
        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {
        Menu::enable('leescore');
        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {
        Menu::disable('leescore');
        return true;
    }

    /**
     * 映射商城分类菜单
     * app_init
     */
    public function appInit()
    {
        return true;
    }


    /**
     * 实现钩子方法
     * @return string
     */
    public function leescorehook($param)
    {
        //获取插件配置信息
        $cfg = $this->getConfig();

        //检测用户是否上传入口图片
        $img = !empty($cfg['enterimg']) ? $cfg['enterimg'] : cdnurl('/assets/addons/leescore/img/scoregoods.png');
        $this->assign('path', $img);
        return $this->fetch('view/hook');
    }

    /**
     * 会员中心操作菜单追加
     * @return mixed
     * @throws \Exception
     */
    public function userSidenavAfter()
    {
        $request = Request::instance();
        $actionname = strtolower($request->action());
        $data = [
            'actionname' => $actionname
        ];

        return $this->fetch('view/user_left_nav', $data);
    }

}

<?php

namespace addons\faqueue;

use app\common\library\Menu;
use think\Addons;

/**
 * 插件
 */
class Faqueue extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu = [
            [
                'name'   => 'faqueue',
                'title'  => '消息队列',
                'ismenu' => 1,
                'icon'   => 'fa fa-list',
                'remark' => '消息队列',
                'sublist' => [
                    [
                        'name' => 'faqueue/log',
                        'title' => '任务完成记录',
                        'sublist' => [
                            ['name' => 'faqueue/log/index', 'title' => '查看'],
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
        Menu::delete('faqueue');
        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {
        Menu::enable('faqueue');
        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {
        Menu::disable('faqueue');
        return true;
    }


}

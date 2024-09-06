<?php

namespace addons\leesign;

use app\common\library\Menu;
use think\Addons;

/**
 * 签到插件
 */
class Leesign extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu = [
            [
                'name'    => 'leesign',
                'title'   => '签到管理',
                'icon'    => 'fa fa-calendar-check-o',
                'sublist' => [
                    ['name' => 'leesign/index', 'title' => '查看'],
                    ['name' => 'leesign/add', 'title' => '添加'],
                    ['name' => 'leesign/edit', 'title' => '修改'],
                    ['name' => 'leesign/del', 'title' => '删除'],
                    ['name' => 'leesign/multi', 'title' => '批量更新'],
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
        Menu::delete('leesign');
        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {
        Menu::enable('leesign');
        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {
        Menu::disable('leesign');
        return true;
    }

    /**
     * 实现钩子方法
     * @return string
     */
    public function leesignhook($param)
    {
        //获取插件配置信息
        $cfg = $this->getConfig();

        //检测用户是否上传入口图片
        $img = !empty($cfg['enterimg']) ? $cfg['enterimg'] : cdnurl('/assets/addons/leesign/img/leesign.png');

        $this->assign('path', $img);
        return $this->fetch('view/hook');
    }

}

<?php

namespace addons\epay;

use addons\epay\library\Service;
use think\Addons;
use think\Config;
use think\Loader;

/**
 * 微信支付宝整合插件
 */
class Epay extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {
        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {
        return true;
    }

    // 支持自定义加载
    public function epayConfigInit()
    {
        $this->actionBegin();
    }

    // 插件方法加载开始
    public function addonActionBegin()
    {
        $this->actionBegin();
    }

    // 模块控制器方法加载开始
    public function actionBegin()
    {
        //添加命名空间
        if (!class_exists('\Yansongda\Pay\Pay')) {

            //SDK版本
            $version = Service::getSdkVersion();

            $libraryDir = ADDON_PATH . 'epay' . DS . 'library' . DS;
            Loader::addNamespace('Yansongda\Pay', $libraryDir . $version . DS . 'Yansongda' . DS . 'Pay' . DS);

            $checkArr = [
                '\Hyperf\Context\Context'     => 'context',
                '\Hyperf\Contract\Castable'   => 'contract',
                '\Hyperf\Engine\Constant'     => 'engine',
                '\Hyperf\Macroable\Macroable' => 'macroable',
                '\Hyperf\Pimple\Container'    => 'pimple',
                '\Hyperf\Utils\Arr'           => 'utils',
            ];
            foreach ($checkArr as $index => $item) {
                if (!class_exists($index)) {
                    Loader::addNamespace(substr($index, 1, strrpos($index, '\\') - 1), $libraryDir . 'hyperf' . DS . $item . DS . 'src' . DS);
                }
            }

            if (!class_exists('\Yansongda\Supports\Logger')) {
                Loader::addNamespace('Yansongda\Supports', $libraryDir . $version . DS . 'Yansongda' . DS . 'Supports' . DS);
            }

            // V3需载入辅助函数
            if ($version == Service::SDK_VERSION_V3) {
                require_once $libraryDir . $version . DS . 'Yansongda' . DS . 'Pay' . DS . 'Functions.php';
            }
        }
    }
}

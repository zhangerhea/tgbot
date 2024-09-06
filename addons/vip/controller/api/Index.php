<?php

namespace addons\vip\controller\api;

use addons\vip\library\Service;
use addons\vip\model\Record;
use addons\vip\model\Vip;

class Index extends Base
{

    protected $noNeedLogin = ['index'];

    /**
     * VIP列表
     */
    public function index()
    {
        $config = get_addon_config('vip');
        $dataList = Vip::where('status', 'normal')->field('sales', true)->order('level', 'asc')->select();
        $vipList = [];
        foreach ($dataList as $index => $item) {
            $rightdata = $item['rightdata'];
            foreach ($rightdata as &$res) {
                $res['image'] = cdnurl($res['image'], true);
            }
            $vipList[] = [
                'level'     => $item['level'],
                'icon'      => $item['icon'],
                'name'      => $item['name'],
                'image'     => $item['image'],
                'intro'     => $item['intro'],
                'rightdata' => $rightdata,
                'pricedata' => $item['pricedata']
            ];
        }
        $vipInfo = Service::getVipInfo();
        $vipInfo = $vipInfo ?: null;

        $paytypelist = array_filter(explode(',', $config['paytypelist']));
        $defaultpaytype = $config['defaultpaytype'];
        $defaultpaytype = in_array($defaultpaytype, $paytypelist) ? $defaultpaytype : reset($paytypelist);

        $this->success('', [
            'vipList' => $vipList,
            'vipInfo' => $vipInfo,
            'config'  => [
                'paytypelist'    => $paytypelist,
                'defaultpaytype' => $defaultpaytype
            ]
        ]);
    }
}

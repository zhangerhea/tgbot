<?php

namespace addons\leescore\controller;

use think\addons\Controller;
use think\Db;

/**
 * 积分商城主页
 * By:龙组的赵日天
 * Time: 2018-12-14
 * Version: v1.1.0
 */
class Index extends Base
{

    protected $model = null;
    protected $layout = 'default';

    public function _initialize()
    {
        parent::_initialize();
        if (!$this->userSign()) {
            die($this->msgFailed(ReturnCode::DOES_NOT_EXIST, '请安装“ 每日签到 ”插件', '', true));
        }

        $adsModel = model('addons\leescore\model\Ads');
        $cate = $this->getCateList(10);

        $other = $adsModel->getAdsList('other');
        $slider = $adsModel->getAdsList('slider');
        $activity = $adsModel->getAdsList('activity');
        //dump($cate);
        $this->view->assign('cate', $cate);
        $this->view->assign('slider', $slider);
        $this->view->assign('activity', $activity);
        $this->view->assign('other', $other);
    }


    /**
     * 检查每日签到插件是否存在
     */
    public function userSign()
    {
        $info = get_addon_info('leesign');
        if($info && isset($info['state']) && (int) $info['state'] === 1){
            return true;
        }else{
            return false;
        }
    }

    public function index()
    {
        //热门商品
        $hotList = $this->getGoodsData(2, 'hot', 'usenum desc');
        //推荐商品
        $recomm = $this->getGoodsData(2, 'recommend', 'weigh desc');
        //首页推荐商品
        $index = $this->getGoodsData(2, 'index', 'weigh desc');

        $mobile_cate = $this->getCateList(10, 1, true);
        $this->view->assign('mobile_cate', $mobile_cate);
        //最新推荐商品
        $new = $this->getGoodsData(2, 'new', 'id desc');

        $this->view->assign('recommend', $recomm);
        $this->view->assign('newlist', $new);
        $this->view->assign('indexlist', $index);
        $this->view->assign('title', __('home'));
        $this->view->assign('hotList', $hotList);
        return $this->fetch("index");
    }

}

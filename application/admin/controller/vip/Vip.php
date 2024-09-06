<?php

namespace app\admin\controller\vip;

use app\common\controller\Backend;

/**
 * VIP等级管理
 *
 * @icon fa fa-circle-o
 */
class Vip extends Backend
{

    /**
     * Vip模型对象
     * @var \app\admin\model\vip\Vip
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\vip\Vip;
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->assignconfig("customColor", \app\admin\model\vip\Vip::getCustomColor());
    }

}

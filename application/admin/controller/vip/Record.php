<?php

namespace app\admin\controller\vip;

use addons\vip\library\Service;
use app\common\controller\Backend;

/**
 * VIP记录管理
 *
 * @icon fa fa-circle-o
 */
class Record extends Backend
{

    /**
     * Record模型对象
     * @var \app\admin\model\vip\Record
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\vip\Record;
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->assignconfig("customColor", \app\admin\model\vip\Vip::getCustomColor());
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            $this->relationSearch = true;
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                ->with(['user', 'vip'])
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);
            foreach ($list as $index => $item) {
                if ($item->user) {
                    $item->user->visible(['nickname']);
                }
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 判断并刷新会员过期时间
     */
    public function recheck()
    {
        $time = time();
        //清理锁定且过期的数据
        \addons\vip\model\Record::where('status', 'locked')->where('expiretime', '<', $time)->update(['status' => 'expired']);

        //判断用户VIP是否过期，-1表示全部
        Service::checkVipExpired(-1);
        $this->success("操作成功");
    }
}

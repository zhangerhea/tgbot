<?php

namespace app\admin\controller\vip;

use app\common\controller\Backend;

/**
 * VIP订单管理
 *
 * @icon fa fa-circle-o
 */
class Order extends Backend
{

    /**
     * Order模型对象
     * @var \app\admin\model\vip\Order
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\vip\Order;
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

}

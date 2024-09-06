<?php

namespace app\admin\controller\leescore;

use app\common\controller\Backend;
use think\Db;
use app\common\model\User;
use app\admin\model\LeescoreOrderGoods;
use app\admin\model\LeescoreGoods;

/**
 *
 *
 * @icon fa fa-circle-o
 */
class Leescoreorder extends Backend
{

    /**
     * ScoreOrder模型对象
     */
    protected $model = null;
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('LeescoreOrder');
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    //订单审核未通过
    public function faild()
    {
        $id = input('post.ids');
        $other = input('post.result_other');
        $row = $this->model->where("id", $id)->find();

        $order_model = new LeescoreOrderGoods();
        $_order_goods = $order_model->where('order_id', $row['id'])->select();

        // var_dump($_order_goods);
        if ($row['status'] == 0) {
            $data = ['result_other' => $other, 'status' => '-1'];
            Db::name('leescore_order')->where("id", $id)->update($data);
            foreach ($_order_goods as $k => $v) {
                Db::name('leescore_goods')->where('id', $v['goods_id'])->setInc('stock', $v['buy_num']);
                Db::name('leescore_goods')->where('id', $v['goods_id'])->setDec('usenum', $v['buy_num']);
            }

        } else {
            $score_log = new User();
            $data = ['result_other' => $other, 'status' => '-2'];
            // 启动事务
            Db::startTrans();
            try {
                Db::name('leescore_order')->where("id", $row['id'])->update($data);

                //写入积分日志
                $score_log::score($row['score_total'], $row['uid'], '订单驳回返还积分');
                $score_log::score($row['money_total'], $row['uid'], '订单驳回返还金额');
                foreach ($_order_goods as $k => $v) {
                    Db::name('leescore_goods')->where('id', $v['goods_id'])->setInc('stock', $v['buy_num']);
                    Db::name('leescore_goods')->where('id', $v['goods_id'])->setDec('usenum', $v['buy_num']);
                }

                Db::commit();

            } catch (\Exception $e) {
                // 回滚
                Db::rollback();
                $this->error($e);
            }
            $this->success(__('order faild tip success'));
        }
    }


    /*发货*/
    public function send()
    {
        if ($this->request->isPost()) {
            $id = input('post.ids');
            $row = $this->model->find($id);
            $status = 2;

            $data['status'] = $status;
            $data['result_other'] = input('post.virtual_other');
            $data['virtual_go_time'] = time();

            if ($status == 3) {
                $data['virtual_sign_time'] = time();
            }
            $data['virtual_sn'] = input('post.virtual_sn');
            $data['virtual_name'] = input('post.virtual_name');

            $this->model->where("id = $id")->update($data);
            $this->success();
        }

        $param = $this->request->param();
        $row = $this->model->with('getOrderGoods,addressInfo')->find($param['ids']);
        $this->view->assign('vo', $row);
        return $this->view->fetch();
    }


    /**
     * 删除
     */
    public function del($ids = "")
    {
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $count = $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();
            $count = 0;
            foreach ($list as $k => $v) {
                $count += $v->delete();

                Db::name('leescore_order_goods')->where('order_id', $v['id'])->delete();
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }


    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('order_id');
            $total = $this->model
                ->with('user,addressInfo')
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with('user,addressInfo')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch();
    }

    public function getOrderGoods()
    {
        $id = input('post.id');
        $data = model('leescoreOrderGoods')->getOrderGoods($id);
        return json($data);
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $w['id'] = $ids;
        $row = $this->model->get($ids);
        //$row->user->username;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($row->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * Selectpage搜索
     *
     * @internal
     */
    public function selectpage()
    {
        return parent::selectpage();
    }
}

<?php

namespace addons\leescore\controller;

use think\addons\Controller;
use think\Db;
use think\Config;

/**
 * 积分商城购物车管理
 * By:龙组的赵日天
 * Time: 2018-12-14
 * Version: v1.1.0
 */
class Cart extends Base
{
    protected $relationSearch = true;
    protected $model = null;
    protected $member = null;
    protected $cart = null;
    protected $layout = 'user-layout';

    public function _initialize()
    {
        parent::_initialize();

        if (!$this->auth->isLogin()) {
            $this->redirect("index/user/login");
        }
        $this->member = $this->auth->getUserInfo();
        $this->model = model('addons\leescore\model\Cart');
    }

    //购物车列表
    public function index()
    {
        //当前会员的购物车
        $w['uid'] = $this->auth->id;

        $list = $this->model
            ->with('goodsDetail')
            ->where($w)
            ->order('createtime desc')
            ->paginate(15, false, ['param' => $this->request->param()]);
        //dump($list);
        $this->view->assign('list', $list);
        return $this->fetch();
    }

    public function add()
    {
        $id = $this->request->param('id');
        $w['goods_id'] = $id;
        $w['uid'] = $this->auth->id;
        $re = Db::name('leescore_cart')->where($w)->find();
        if ($re) {
            Db::name('leescore_cart')->where($w)->setInc('number', 1);
            return json(['status' => true, 'msg' => 'success']);
        }
        $data['uid'] = $this->auth->id;
        $data['goods_id'] = $id;
        $data['number'] = 1;
        $data['createtime'] = time();
        if ($this->model->insert($data)) {
            return json(['status' => true, 'msg' => 'success']);
        } else {
            return json(['status' => false, 'msg' => 'faild']);
        }
    }

    //删除
    public function delete()
    {
        $ids = $this->request->param()['ids'];
        try {
            $w['id'] = array('in', $ids);
            $w['uid'] = $this->member['id'];
            $this->model->where($w)->delete();
            $res = json(['status' => true, 'msg' => 'Success']);
        } catch (\Exception $e) {
            dump($e->getMessage());
        }

        return $res;
    }

    //单条订单删除
    public function delone()
    {
        $id = input('get.id') ? input('get.id') : false;
        if (!$id) {
            return json(['status' => false, 'msg' => 'ID参数不能为空']);
        }

        try {
            $w['id'] = $id;
            $w['uid'] = $this->member['id'];
            $this->model->where($w)->update(['isdel' => 1]);
            $res = json(['status' => true, 'msg' => 'success']);
        } catch (\Exception $e) {
            dump($e->getMessage());
        }
        return $res;
    }

    //关闭订单
    public function closeOrder()
    {
        $id = input('get.id') ? input('get.id') : false;
        if (!$id) {
            return json(['status' => false, 'msg' => 'ID参数不能为空']);
        }

        try {
            $w['id'] = $id;
            $w['uid'] = $this->member['id'];
            $this->model->where($w)->update(['status' => '-1']);
            $res = json(['status' => true, 'msg' => 'success']);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        return $res;
    }

    public function getUserCartTotal()
    {
        $userid = $this->member['id'];
        $total = $this->model->getUserCartTotal($userid);
        return $total;
    }
}

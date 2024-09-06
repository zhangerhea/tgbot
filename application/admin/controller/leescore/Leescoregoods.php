<?php

namespace app\admin\controller\leescore;

use app\admin\library\Auth;
use app\common\controller\Backend;
use fast\Tree;
use app\admin\model\leescoreCategory;

/**
 * 商品
 *
 * @icon fa fa-circle-o
 */
class Leescoregoods extends Backend
{

    /**
     * ScoreGoods模型对象
     */
    protected $model = null;
    protected $opt = null;
    protected $multiFields = "category_id";

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('leescoreCategory');
        $disabledIds = [];
        $cate = $this->model->getLeescoreCategory();

        $tree = Tree::instance()->init($cate, 'category_id');
        $this->categorylist = $tree->getTreeList($tree->getTreeArray(0), 'name');
        $categorydata = [];
        foreach ($this->categorylist as $k => $v) {
            $categorydata[$v['id']] = $v;
        }

        $this->assignconfig('options_val', $categorydata);

        $this->opt = $categorydata;
        $this->assign('options', $categorydata);
        $this->model = model('LeescoreGoods');
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("payTypeList", $this->model->getPayTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("flagList", $this->model->getFlagList());
    }

    public function getOptions()
    {
        $json = [];
        $opt = $this->opt;
        foreach ($opt as $key => $val) {
            $json[$key . "-n"] = $val['name'];
        }
        return json($json);
    }

    public function index()
    {

        //设置过滤方法
        if ($this->request->isAjax()) {
            //先转换为数组
            $filter = json_decode($this->request->request('filter'), true);
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            //构造父类select列表选项数据
            $list = [];
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('name');
            $total = $this->model
                ->with('getLeescoreGoods')
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->with('getLeescoreGoods')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


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

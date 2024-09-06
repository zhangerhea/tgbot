<?php

namespace app\admin\controller\leescore;

use app\common\controller\Backend;
use think\Db;
use fast\Tree;
use think\Exception;
use think\exception\PDOException;

/**
 *
 *
 * @icon fa fa-circle-o
 */
class Leescorecategory extends Backend
{

    /**
     * ScoreCategory模型对象
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->request->filter(['strip_tags']);
        $this->model = model('leescoreCategory');
        $cate = $this->model->getLeescoreCategory();

        $tree = Tree::instance()->init($cate, 'category_id');
        $this->categorylist = $tree->getTreeList($tree->getTreeArray(0), 'name');
        $categorydata = [];
        foreach ($this->categorylist as $k => $v) {
            $categorydata[$v['id']] = $v;
        }

        //dump($cate[0]->get_cate_name->name);
        $this->assign('options', $categorydata);
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        if ($this->request->isAjax()) {
            $search = $this->request->request("search");
            $type = $this->request->request("type");
            //构造父类select列表选项数据
            $list = [];

            foreach ($this->categorylist as $k => $v) {
                if ($search) {
                    if ($v['type'] == $type && stripos($v['name'], $search) !== false || stripos($v['nickname'], $search) !== false) {
                        if ($type == "all" || $type == null) {
                            $list = $this->categorylist;
                        } else {
                            $list[] = $v;
                        }
                    }
                } else {
                    if ($type == "all" || $type == null) {
                        $list = $this->categorylist;
                    } else {
                        if ($v['type'] == $type) {
                            $list[] = $v;
                        }
                    }

                }

            }
            $total = count($list);
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch();
    }


    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                if (intval($params['category_id']) != 0) {
                    $i = 0;
                    $rs = false;
                    while ($i < 10) {
                        $cid = !$rs ? $params['category_id'] : $rs['category_id'];
                        $rs = Db::name('leescore_category')->where('id', $cid)->find(); // 遍历分类层级
                        if ($rs['category_id'] == 0) {
                            $params['topid'] = $rs['id'];
                            break;
                        }
                        $i++;
                    }
                } else {
                    $params['topid'] = 0;
                }

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                // var_dump($params);
                if ($params['topid'] === false) $this->error("添加失败。");
                // var_dump($params);
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : true) : $this->modelValidate;
                        $this->model->validate($validate);
                    }

                    $result = $this->model->allowField(true)->save($params);

                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($this->model->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            //$this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);

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
            $id = $params['id'];
            $rows = $this->model->whereOr(['category_id' => $id, 'topid' => $id])->where(['id' => ['neq', $id]])->select();
            if ($rows) $this->error("存在子分类的分类不允许调整位置，请删除子分类再修改。");

            if ($params) {
                if ((int)$params['category_id'] != 0) {
                    $i = 0;
                    $rs = false;
                    while ($i < 10) {
                        $cid = !$rs ? $params['category_id'] : $rs['category_id'];
                        $rs = Db::name('leescore_category')->where('id', $cid)->find(); // 遍历分类层级
                        if ($rs['category_id'] == 0) {
                            $params['topid'] = $rs['id'];
                            break;
                        }
                        $i++;
                    }
                } else {
                    $params['topid'] = 0;
                }
                if ($params['topid'] === false) $this->error("保存失败.");

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
     * 删除
     */
    public function del($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();

            $is_del = true;
            foreach ($list as $key => $val) {
                $rows = $this->model->whereOr(['category_id' => $val['id'], 'topid' => $val['id']])->where(['id' => ['neq', $val['id']]])->select();
                if ($rows) {
                    $is_del = false;
                    break;
                }
            }

            if (!$is_del) $this->error("要删除的分类中存在子分类，请先删除子分类。", 'ids');
            $count = 0;
            Db::startTrans();
            try {
                foreach ($list as $k => $v) {
                    $count += $v->delete();
                }
                Db::commit();
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
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
     * Selectpage搜索
     *
     * @internal
     */
    public function selectpage()
    {
        return parent::selectpage();
    }

}

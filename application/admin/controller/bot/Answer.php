<?php

namespace app\admin\controller\bot;

use app\common\controller\Backend;
use app\admin\model\bot\Bot;

/**
 * 应答管理
 *
 * @icon fa fa-circle-o
 */
class Answer extends Backend
{

    /**
     * Answer模型对象
     * @var \app\admin\model\bot\Answer
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\bot\Answer;
        $this->view->assign("reqeustTypeList", $this->model->getReqeustTypeList());
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

        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            /* $filter = json_decode($this->request->get('filter'),true);
             if(empty($filter['bot_id'])){
                 $filter["bot_id"] = $bot_id;
                 $this->request->get(["filter"=>json_encode($filter)]);
             }*/
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            //print_r($where->bot_id);

            $list = $this->model->field('*')
                ->with(['bot' => function ($query) {
                    $query->withField('bot_name');
                }])
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);

            // echo $this->model->getLastSql();

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        $bot_list = Bot::where(['status' => 'normal'])->column('id,bot_name');
        //unset($bot_list[$bot_id]);
        $this->assign('botList', $bot_list);

        return $this->view->fetch();
    }

    public function edit($ids = null)
    {
        if (false === $this->request->isPost()) {
            $bot_id = $this->model->where('id', $ids)->value('bot_id');
            $bot = Bot::where('id', $bot_id)->field('id,bot_name,token')->find();

            $this->assign("bot", $bot);

        }

        return parent::edit($ids);

    }

    public function add()
    {
        if (false === $this->request->isPost()) {
            $bot = Bot::where('status', 'normal')->field('id,bot_name,token')->find();
            $this->assign("bot", $bot);
            return $this->view->fetch();
        }

        return parent::add();

    }

    /** 回复按钮
     * @param $ids
     * @return string|void
     * @throws \think\Exception
     */
    public function buttons($ids)
    {
        if (false === $this->request->isPost()) {

            $buttons = $this->model->where('id', $ids)->value('buttons');
            if(empty($buttons)){
                $buttons='[]';
            }
            $this->view->assign('buttons', $buttons);
            return $this->view->fetch();
        }

        // print_r(json_encode(array_values($this->request->param('buttons/a'))));return;
        $buttons_json = $this->request->post('buttons/a');
        $buttons_json = array_values($buttons_json);
        foreach ($buttons_json as &$buttons) {
            $buttons = array_values($buttons);
            foreach ($buttons as &$button){
                if(empty($button['web_app']['url'])){
                    unset($button['web_app']);
                }
            }
        }
        $this->model->where('id', $ids)->update(['buttons' => json_encode($buttons_json)]);

        $this->success('保存成功');
    }

    /**
     * 键盘
     * @param $ids
     * @return string|void
     * @throws \think\Exception
     */
    public function keyboard($ids)
    {
        if (false === $this->request->isPost()) {

            $keyboard = $this->model->where('id', $ids)->value('keyboard');
            if(empty($keyboard)){
                $keyboard='[]';
            }
            $this->view->assign('keyboard', $keyboard);
            return $this->view->fetch();
        }

        // print_r(json_encode(array_values($this->request->param('buttons/a'))));return;
        $keyboard_json = $this->request->post('keyboard/a');
        $keyboard_json = array_values($keyboard_json);
        foreach ($keyboard_json as &$buttons) {
            $buttons = array_values($buttons);
            foreach ($buttons as &$button){
                if(empty($button['web_app']['url'])){
                    unset($button['web_app']);
                }
            }
        }
        $this->model->where('id', $ids)->update(['keyboard' => json_encode($keyboard_json)]);

        $this->success('保存成功');
    }
}

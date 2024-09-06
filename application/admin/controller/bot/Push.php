<?php

namespace app\admin\controller\bot;

use app\admin\model\bot\Bot;
use app\admin\model\User;
use app\common\controller\Backend;
use think\Queue;

/**
 * 消息推送
 *
 * @icon fa fa-circle-o
 */
class Push extends Backend
{

    /**
     * Push模型对象
     * @var \app\admin\model\bot\Push
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\bot\Push;
        $this->view->assign("statusList", $this->model->getStatusList());
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
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                ->with(['bot' => function ($query) {
                    $query->withField('bot_name');
                }])
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);


            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        $bot_list = Bot::where(['status' => 'normal'])->column('id,bot_name');

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

    /**
     * 立即推送
     * @return void
     */
    public function send($ids = null)
    {
        $job = 'addons\faqueue\library\jobs\TelegramJob';
        //查询所有电报用户

        //查询当前推送消息内容
        $push = $this->model->where('id', $ids)->field('bot_id,text')->find();
        $text = $push['text'];
        $data['bot_id'] = $push['bot_id'];
        $data['push_id'] = $ids;


        $where['telegram'] = ['neq', ''];

        $users = User::where($where)->field('telegram,first_name,last_name')->select();
        foreach ($users as $user) {
            $data['chat_id'] = $user['telegram'];
            //处理标签
            $text = str_replace('{{first_name}}', $user['first_name'], $text);
            $data['text'] = str_replace('{{last_name}}', $user['last_name'], $text);

            Queue::push($job, $data);
        }
        $total_count=count($users);
        $this->model->where('id', $ids)->update(['status' => 'sending','total_count'=>$total_count]);

        $this->success('推送中...');
    }
}

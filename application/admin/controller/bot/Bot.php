<?php

namespace app\admin\controller\bot;


use app\admin\model\bot\Bot as BotModel;
use app\common\controller\Backend;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use think\Db;
use think\Exception;

/**
 * 机器人
 *
 * @icon fa fa-circle-o
 */
class Bot extends Backend
{

    /**
     * Bot模型对象
     * @var \app\admin\model\bot\Bot
     */
    protected $model = null;

    protected $noNeedRight = 'searchlist';
    protected $selectpageFields = "id,bot_name,token";
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\bot\Bot;

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
                ->where($where)
                ->field('id, token, bot_name, create_time,buttons, status')
                ->order($sort, $order)
                ->paginate($limit);


            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }

        $this->assignconfig('api_url',config('site.api_url'));
        return $this->view->fetch();
    }

    /**
     * 添加
     * @throws Exception
     */
    public function add()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }

        $this->token();
        $params = $this->request->post('row/a');
        $token = $params['token'];
        $bot_name = $params['bot_name'];
        //检查是否存在
        $n = $this->model->where('token', $token)->count();
        if ($n > 0) {
            $this->error('token已经存在');
        }
        $data['bot_name'] = $bot_name;
        $data['token'] = $token;
        $this->model->save($data);

        $this->success();

    }

    /**
     * 修改
     * @throws Exception
     */
    public function edit($ids = null)
    {
        if (false === $this->request->isPost()) {
            $bot = $this->model->find($ids);
            $this->view->assign('row', $bot);
            return $this->view->fetch();
        }

        $this->token();
        $params = $this->request->post('row/a');
        $token = $params['token'];
        $bot_name = $params['bot_name'];
        //检查是否存在
        $where['token'] = $token;
        $where['id'] = ['neq', $ids];
        $n = $this->model->where($where)->count();
        if ($n > 0) {
            $this->error('token已经存在');
        }
        $data['bot_name'] = $bot_name;
        $data['token'] = $token;
        $this->model->where('id', $ids)->update($data);

        $this->success();

    }

    public function changeStatus($ids)
    {

        $status = BotModel::where(['id' => $ids])->value('status');


        if ($status == 'normal') {
            $normal_count = BotModel::where(['status' => 'normal'])->count();
            if ($normal_count <= 1) {
                $this->error('必须有一个开启');
            }
            BotModel::update(['status' => 'hidden'], ['id' => $ids]);
        } else {

         //   $this->model->where('id', 'neq', $ids)->update(['status' => 'hidden']);
            BotModel::update(['status' => 'normal'], ['id' => $ids]);
        }

        $this->success("切换成功");
    }


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
    public function games($ids)
    {
        if (false === $this->request->isPost()) {
            $games = $this->model->where('id', $ids)->value('games');
            $this->view->assign('games', $games);
            return $this->view->fetch();
        }

        $games_json = $this->request->post('games_json');
        $this->model->where('id', $ids)->update(['games' => $games_json]);

        $this->success('保存成功');
    }

    /**
     * @throws TelegramSDKException
     */
    public function getMe($ids)
    {
        $token = $this->model->where('id', $ids)->value('token');
        $telegram = new Api($token);
        $response = $telegram->getMe();
        $data['bot_name'] = $response['first_name'] ?? "未设置机器人";//机器人组名

        $data['username'] = $response['username'] ?? "未设置机器人";//机器人姓名
        $data['can_join_groups'] = $response['can_join_groups'] ?? "未知";//可以加入组织？turn，false
        $data['can_read_all_group_messages'] = $response['can_read_all_group_messages'] ?? "未知";//可以读取所有群组消息吗
        $data['supports_inline_queries'] = $response['supports_inline_queries'] ?? "未知";//支持内联查询

        $this->model->where('id', $ids)->update($data);

        $this->success('更新成功');

    }

    public function del($ids = null)
    {
        $status = $this->model->where('id', $ids)->value('status');
        if ($status != 'hidden') {
            $this->error('启用状态，不准删除');
        }
        Db::startTrans();
        try {

            $this->model->where('id', $ids)->delete();

            \app\admin\model\bot\Answer::where(['bot_id' => $ids])->delete();
            \app\admin\model\bot\Group::where(['bot_id' => $ids])->delete();
            \app\admin\model\bot\Message::where(['bot_id' => $ids])->delete();
            \app\admin\model\bot\Cron::where(['bot_id' => $ids])->delete();
            Db::commit();
        } catch (\Exception $e) {

            $this->error($e->getMessage());
            Db::rollback();
        }
        $this->success('删除成功');
    }

    public function searchlist()
    {
        $result = $this->model->limit(20)->field('id,bot_name')->select();
        $searchlist = [];
        foreach ($result as $key => $value) {
            $searchlist[] = ['id' => $value['id'], 'name' => $value['bot_name']];
        }
        $data = ['searchlist' => $searchlist];
        $this->success('', null, $data);
    }
}

<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\template;
use Smarty;
/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
   /*     $keyboard = [
            [['text' => 'aa', 'web_app' => ["url" => 'https://t.me/Zhangerhe_bot/huawuque?startapp=123456']], ["text" => 'bb', "request_contact" => true], '9'],
            ['4', '5', '6'],
            ['1', '2', '3'],
            ['0']
        ];

        echo json_encode($keyboard);*/
//
//        $t = new template();
//        $t->display('{$title}',['title' => 'afasdfasdfasfd']);
        $update=[];
        if(isset($update['message'])){
         echo 333;
        }
       // $this->success('请求成功');
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: zhoujun
 * Date: 2018/9/1
 * Time: 11:58
 */

namespace addons\faqueue\library\jobs;

use addons\faqueue\model\FaqueueLog;
use app\admin\model\bot\Bot;


use app\admin\model\bot\Push;
use Telegram\Bot\Exceptions\TelegramSDKException;
use think\Log;
use think\queue\job;
use Telegram\Bot\Api;

class TelegramJob
{
    /**
     * @throws TelegramSDKException
     */
    public function fire(Job $job, $data)
    {
        $botModel = new Bot();
        $bot = $botModel->where('id', $data['bot_id'])->field('token,buttons,button_column')->find();
        $token = $bot['token'];

        $telegram = new Api($token);
        //region 处理按钮
        $inline_keyboard = $botModel->getButtons($bot['buttons'], $bot['button_column']);

        $keyboard['inline_keyboard'] = $inline_keyboard;

        $reply_markup = $telegram->inlineKeyboardMarkup($keyboard);
        //endregion
        $response = $telegram->sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => $data['text'],
            "parse_mode" => "Markdown",
            'reply_markup' => $reply_markup
        ]);
        if ($response->getMessageId()) {
            $job->delete();
            (new FaqueueLog())->log($job->getQueue(), $job->getName(), $data);
            //检查数量
            $push = new Push();
            $_push_id = $data['push_id'];
            $push->where('id', $_push_id)->setInc('push_count');

            $push_info = $push->where('id', $_push_id)->field('total_count,push_count')->find();
            if ($push_info['total_count'] == $push_info['push_count']) {
                $push->where('id', $_push_id)->update(['status' => 'finished']);
            }
        } else {
            $job->release();
            Log::write("电报发送失败：" . print_r([
                    'name' => $job->getName(),
                    'error' => ''
                ], true), 'error');
        }

    }

    public function send(Job $job, $data)
    {

    }

    public function failed($data)
    {
        Log::write("电报任务失败：" . print_r(['data' => $data,], true), 'error');
    }
}
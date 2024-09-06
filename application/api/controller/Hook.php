<?php

namespace app\api\controller;

use app\admin\model\bot\Answer;
use app\admin\model\bot\Bot;
use app\common\controller\Api;
use Telegram\Bot\Exceptions\TelegramResponseException;
use Telegram\Bot\Exceptions\TelegramSDKException;
use think\Db;
use Telegram\Bot\Api as BotApi;
use think\Log;

/**
 * webhook接口
 */
class Hook extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     * @throws TelegramSDKException
     */
    public function do()
    {

        //获取反射信息
        $input = file_get_contents('php://input');
        $update = json_decode($input, true);

        $myfile = fopen("video_check_log.txt", "w+") or die("Unable to open file!");
        fwrite($myfile, $input);
        fclose($myfile);

        //测试
        //$update = '{"update_id":286830093,"message":{"message_id":98,"from":{"id":6453720352,"is_bot":false,"first_name":"\u4e8c\u6cb3","last_name":"\u5f20","username":"zhangerhea","language_code":"zh-hans"},"chat":{"id":6453720352,"first_name":"\u4e8c\u6cb3","last_name":"\u5f20","username":"zhangerhea","type":"private"},"date":1721984727,"text":"xcvz"}}';
        // $update = json_decode($update, true);
        $id = $this->request->get('id');

        $botModel = new Bot();
        $bot = $botModel->where('id', $id)->find();

        if (empty($bot) || empty($bot['token'])) {
            $this->error('token is null');
        }
        //  die;
        $token = $bot['token'];

        $telegram = new BotApi($token);


        $message_id = '';
        if (isset($update['message'])) {
            $message_id = $update['message']['message_id'];
        }

        if (isset($update["callback_query"])) {
            //游戏
            if (isset($update['callback_query']['game_short_name'])) {
                $games = $bot['games'];
                $games = json_decode($games, true);
                $_url = '';
                if ($games) {
                    foreach ($games as $game) {
                        if ($game['game_short_name'] == $update['callback_query']['game_short_name'] && $game['state'] === 1) {
                            $_url = $game['url'];
                            break;
                        }
                    }
                }
                if ($_url) {
                    $param['callback_query_id'] = $update['callback_query']['id'];
                    $param['show_alert'] = false;
                    $param['text'] = 'hi:' . $update['callback_query']['from']['first_name'];
                    $param['url'] = $_url . '#' . http_build_query($update['callback_query']['from']);

                    $rt = $telegram->answerCallbackQuery($param);

//               Log::write($rt);

                }
                $this->success('ok');
            }

            //按钮回调
            $name = $update['callback_query']['message']['chat']['first_name'];
            $chat_id = $update['callback_query']['message']['chat']['id'];
            $text = $update['callback_query']['data'] ?? "凤雏";//获取用户消息

            $message_id = $update['callback_query']['message']['message_id'];

        } else if (isset($update['my_chat_member'])) {
            //新成员 刚拉入
            //
            $name = $update['my_chat_member']['chat']['first_name'];
            $chat_id = $update['my_chat_member']['chat']['id'];
            $text = "凤雏";//获取用户消息


        } else if (isset($update['edited_message'])) {
            //群组

            if (isset($update['edited_message']['game'])) {
                $name = $update['edited_message']['game']['title'];
                //获取用户消息
            } else {
                $name = $update['edited_message']['chat']['title'];
                //获取用户消息
            }
            $chat_id = $update['edited_message']['chat']['id'];
            $text = $update['edited_message']['text'] ?? "凤雏";

        } else if (isset($update['message'])) {
            //私信
            $chat_id = $update['message']['chat']['id'];
            $name = $update['message']['from']['first_name'] ?? $update['message']['chat']['title'];
            $text = $update['message']['text'] ?? "凤雏";//获取用户消息
            if ($update['message']['chat']['type'] == "group" || $update['message']['chat']['type'] == "supergroup") {
                $chat_title = $update['message']['chat']['title'];
                $chat = Db::table('fa_bot_group')->where(['chat_id' => $chat_id, 'bot_id' => $id])->select();
                if (count($chat) == 0) {
                    $group['chat_title'] = $update['message']['chat']['title'];
                    $group['chat_id'] = $update['message']['chat']['id'];
                    $group['bot_id'] = $id;
                    Db::table('fa_bot_group')->insert($group);
                } else {
                    Db::table('fa_bot_group')->where(['chat_id' => $update['message']['chat']['id'], 'bot_id' => $id])->update(['chat_title' => $update['message']['chat']['title']]);
                }

            }
            //

            //
        } else if (isset($update['channel_post'])) {
            //     //群组

            $name = $update['channel_post']['chat']['title'];
            $chat_id = $update['channel_post']['chat']['id'];
            $text = $update['channel_post']['text'] ?? "凤雏";//获取用户消息

            if (strpos($chat_id, '-') !== false) {
                $chat = Db::table('fa_bot_group')->where(['chat_id' => $chat_id, 'bot_id' => $id])->select();
                if (count($chat) == 0) {

                    $group['chat_title'] = $update['channel_post']['chat']['title'] ?? "错误";
                    $group['chat_id'] = $update['channel_post']['chat']['id'] ?? "错误";
                    $group['bot_id'] = $id ?? 0;
                    $chat_title = $group['chat_title'];
                    try {
                        $articles = Db::table('fa_bot_group')->strict(false)->insert($group);
                    } catch (\Exception $e) {
                        // $this->error($e);

                    }


                } else {
                    Db::table('fa_bot_group')->where(['chat_id' => $update['channel_post']['chat']['id'], 'bot_id' => $id])
                        ->update(['chat_title' => $update['channel_post']['chat']['title']]);
                }
            }


        } else {
            //  $text = "凤雏";
            //  $name = "zzznl";
            //  $chat_id = '-1001321849310';
            return "无";
        }


        $data['text'] = $text ?? "凤雏";
        $data['name'] = $name ?? "zzznl";
        $data['chat_id'] = $chat_id ?? "0";
        $data['chat_title'] = $chat_title ?? "";
        $data['bot_id'] = $id;
        $data['send_time'] = time();

        $tg_message = Db::table('fa_bot_message')->insert($data);

        //获取数据库关键词
        $answer = (new Answer())->where(['keywords' => $data['text'], 'bot_id' => $id])->field('text,sendtype,isforce,isquote,buttons,keyboard')->find();

        if (empty($answer)) {
            $this->error('answer is null');
        }

        // $api['text'] = str_replace("br", '\n',$api['text']);

        //todo 处理标签

        // 机器人按钮
        $inline_keyboard = $bot['buttons'];
        //如果回复按钮
        if ($answer['buttons']) {
            $inline_keyboard = $answer['buttons'];
        }
        if ($inline_keyboard) {
            $inline_keyboard = json_decode($inline_keyboard, true);
        }

        $keyboard['inline_keyboard'] = $inline_keyboard;

        $reply_markup = $telegram->inlineKeyboardMarkup($keyboard);

        //强制回复
        if ($answer['isforce'] == 1) {
            $reply_markup = $telegram->forceReply();
        }


        //键盘
        if ($answer['keyboard']) {
            $reply_markup = $telegram->replyKeyboardMarkup([
                'keyboard' => json_decode($answer['keyboard'], true),
                'resize_keyboard' => true,
                'one_time_keyboard' => false
            ]);
        }


        try {

            if ($answer['sendtype'] == 'deleteMessage') {
                //删除消息
                $telegram->deleteMessage([
                    'chat_id' => $chat_id,
                    'message_id' => $message_id]);
            } else {
                //region 发送消息
                $param = [
                    'chat_id' => $chat_id,
                    'text' => $answer['text'],
                    "parse_mode" => "Markdown",
                    'reply_markup' => $reply_markup
                ];
                //是否引用
                if ($answer['isquote'] == 1) {
                    $param['reply_to_message_id'] = $message_id;
                }
                $response = $telegram->sendMessage($param);
                //endregion
            }
            $messageId = $response->getMessageId();
        } catch (TelegramResponseException $e) {
            echo $e->getMessage();
            Log::write($e->getMessage());
        }


    }
}

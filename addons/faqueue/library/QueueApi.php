<?php
/**
 * Created by PhpStorm.
 * User: zhoujun
 * Date: 2018/9/1
 * Time: 12:11
 */

namespace addons\faqueue\library;

use addons\faqueue\model\FaqueueLog;
use think\Db;
use think\Queue;

class QueueApi
{

    public static function sendEmail($subject,$to,$message){

        $data = [
            'subject' => $subject,
            'to' => $to,
            'message' => $message,
        ];

        return self::push('addons\faqueue\library\jobs\EmailJob', $data);

    }


    public static function smsSend($mobile,$code = null,$event = 'default'){
        $data = [
            'method' => 'send',
            'mobile' => $mobile,
            'code' => $code,
            'event' => $event,
        ];
        return self::push('addons\faqueue\library\jobs\SmsJob', $data);
    }

    public static function smsNotice($mobile,$msg = '',$template = null){
        $data = [
            'method' => 'notice',
            'mobile' => $mobile,
            'msg' => $msg,
            'template' => $template,
        ];
        return self::push('addons\faqueue\library\jobs\SmsJob', $data);
    }

    public static function push($job, $data = '', $queue = null){
        return Queue::push($job, $data, $queue);
    }

    public static function later($delay, $job, $data = '', $queue = null){
        return Queue::later($delay,$job, $data, $queue);
    }
}
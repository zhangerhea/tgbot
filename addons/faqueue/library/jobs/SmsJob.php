<?php
/**
 * Created by PhpStorm.
 * User: zhoujun
 * Date: 2018/9/1
 * Time: 11:40
 */
namespace addons\faqueue\library\jobs;

use addons\faqueue\model\FaqueueLog;
use app\common\library\Sms;
use think\Log;
use think\queue\job;

class SmsJob
{
    public function fire(Job $job, $data){

        $method = $data['method'];

        switch ($method){
            case 'send':
                $result = Sms::send($data['mobile'],$data['code'],$data['event']);
                break;
            case 'notice':
                $result = Sms::notice($data['mobile'],$data['smg'],$data['template']);
                break;
            default:
                $result = false;
        }

        if($result){
            Log::write("发送成功，result：".print_r($result,true));
            $job->delete();
            (new FaqueueLog())->log($job->getQueue(),$job->getName(),$data);
        }else{
            $job->release();
            Log::write("短信发送失败：".print_r([
                    'name' => $job->getName(),
                    'result' => $result
                ],true),'error');
        }

    }

    public function failed($data){
        Log::write("短信任务失败：".print_r(['data' => $data,],true),'error');
    }
}
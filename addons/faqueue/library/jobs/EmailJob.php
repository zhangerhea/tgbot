<?php
/**
 * Created by PhpStorm.
 * User: zhoujun
 * Date: 2018/9/1
 * Time: 11:58
 */

namespace addons\faqueue\library\jobs;

use addons\faqueue\model\FaqueueLog;
use app\common\library\Email;
use think\Log;
use think\queue\job;

class EmailJob
{
    public function fire(Job $job, $data){

        $email = new Email();

        $result = $email->subject($data['subject'])
            ->to($data['to'])
            ->message($data['message'])
            ->send();

        if($result){
            $job->delete();
            (new FaqueueLog())->log($job->getQueue(),$job->getName(),$data);
        }else{
            $job->release();
            Log::write("邮件发送失败：".print_r([
                    'name' => $job->getName(),
                    'error'=> $email->getError()
                ],true),'error');
        }

    }

    public function failed($data){
        Log::write("邮件任务失败：".print_r(['data' => $data,],true),'error');
    }
}
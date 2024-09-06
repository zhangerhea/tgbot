<?php
/**
 * Created by PhpStorm.
 * User: zhoujun
 * Date: 18-9-4
 * Time: 下午1:44
 */

namespace addons\faqueue\model;

use think\Config;
use think\Db;
use think\Model;

class FaqueueLog extends Model
{

    protected $autoWriteTimestamp = 'int';

    public function log($queue , $job, $data){

        return $this->data([
            'job' => $job,
            'queue' => $queue,
            'data' => is_string($data) ? $data : json_encode($data),
        ])->save();
    }

}
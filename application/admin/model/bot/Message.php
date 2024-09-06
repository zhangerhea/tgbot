<?php

namespace app\admin\model\bot;

use think\Model;


class Message extends Model
{

    

    

    // 表名
    protected $name = 'bot_message';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'send_time_text'
    ];
    

    



    public function getSendTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['send_time']) ? $data['send_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setSendTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function bot()
    {
        return $this->belongsTo('Bot', 'bot_id')->setEagerlyType(0);
    }

}

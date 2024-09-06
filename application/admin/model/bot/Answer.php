<?php

namespace app\admin\model\bot;

use think\Model;


class Answer extends Model
{

    

    

    // 表名
    protected $name = 'bot_answer';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = false;

    // 追加属性


    
    public function getReqeustTypeList()
    {
        return ['common' => __('Common'), 'command' => __('Command')];
    }


    public function getReqeustTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['reqeust_type']) ? $data['reqeust_type'] : '');
        $list = $this->getReqeustTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['update_time']) ? $data['update_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setUpdateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function bot()
    {
        return $this->belongsTo('Bot', 'bot_id')->setEagerlyType(0);
    }
}

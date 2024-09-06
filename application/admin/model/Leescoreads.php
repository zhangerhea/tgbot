<?php

namespace app\admin\model;

use think\Model;

class Leescoreads extends Model
{
    // 表名
    protected $name = 'leescore_ads';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;

    // 追加属性
    protected $append = [
        'open_mode_text',
        'position_text',
        'starttime_text',
        'endtime_text',
        'status_text'
    ];


    public function getOpenModeList()
    {
        return ['0' => __('Open_mode 0'), '1' => __('Open_mode 1')];
    }

    public function getStatusList()
    {
        return ['0' => __('status off'), '1' => __('status on')];
    }

    public function getPositionList()
    {
        return ['slider' => __('Position slider'), 'activicy' => __('Position activicy'), 'other' => __('Position other')];
    }


    public function getOpenModeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['open_mode'];
        $list = $this->getOpenModeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getPositionTextAttr($value, $data)
    {
        $value = $value ? $value : $data['position'];
        $list = $this->getPositionList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStarttimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['starttime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getEndtimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['endtime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setStarttimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setEndtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}

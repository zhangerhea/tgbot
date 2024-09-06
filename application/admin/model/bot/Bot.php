<?php

namespace app\admin\model\bot;

use think\Model;


class Bot extends Model
{

    

    

    // 表名
    protected $name = 'bot';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = 'delete_time';

    // 追加属性
    protected $append = [
        'create_time_text',
        'update_time_text'
    ];
    

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

    /**
     * 组装按钮
     * @param $buttons
     * @param $button_column
     * @return array|void
     */
    public function getButtons($buttons,$button_column)
    {

        $buttons = json_decode($buttons, true);

        $inline_keyboard = [];
        $keyboard = [
            'inline_keyboard' => [
            ]
        ];
        if (empty($buttons)) {
            return;
        }
        foreach ($buttons as $key => $button) {
            if ($button['state'] == 0) {
                unset($buttons[$key]);
            }
            if ($button['type'] == 'inline') {
                //内联

                $buttons[$key]['callback_data'] = $button['cmd'];

            } else {

                $buttons[$key]['url'] = $button['cmd'];
            }
            unset($buttons[$key]['cmd']);
            unset($buttons[$key]['state']);
            unset($buttons[$key]['type']);


        }
        //  print_r($buttons);
        $button_column = $button_column;
        $button_column = explode('|', $button_column);

        foreach ($button_column as $n) {
            $a = array_splice($buttons, 0, $n);
            if ($a) {
                $inline_keyboard[] = $a;
            }
        }
        //endregion

        return $inline_keyboard;
    }

}

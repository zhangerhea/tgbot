<?php

namespace app\admin\model;

use think\Model;


class Link extends Model
{


    // 表名
    protected $name = 'leescore_link';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'target_text'
    ];


    public function getTypeList()
    {
        return ['1' => __('Type 1'), '2' => __('Type 2'), '3' => __('Type 3')];
    }

    public function getTargetList()
    {
        return ['_dialog' => __('Target _dialog'), '_self' => __('Target _self'), '_blank' => __('Target _blank')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getTypeidTextAttr($value, $data)
    {
        $cateModel = new Linkcategory();
        $cate_detail = $cateModel->get($value);
        return $cate_detail['name'];
    }


    public function getTargetTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['target']) ? $data['target'] : '');
        $list = $this->getTargetList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function leescorelinkcategory()
    {
        return $this->belongsTo('app\admin\model\Linkcategory', 'typeid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}

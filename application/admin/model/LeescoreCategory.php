<?php

namespace app\admin\model;

use think\Model;

class LeescoreCategory extends Model
{
    // 表名
    protected $name = 'leescore_category';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    // 追加属性
    protected $append = [

    ];

    //拖动排序
    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    public function getLeescoreCategory()
    {
        $list = collection(self::order('weigh', 'asc')->select())->toArray();
        return $list;
    }

    public function getCategoryIdTextAttr($val)
    {
        $row = $this->get($val);
        return $row['name'];
    }
}

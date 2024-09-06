<?php

namespace app\admin\model\vip;

use think\Exception;
use think\Model;


class Vip extends Model
{

    // 表名
    protected $name = 'vip';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text'
    ];

    public static function init()
    {
        self::beforeWrite(function ($row) {
            $exist = self::where(function ($query) use ($row) {
                $query->where('level', $row['level']);
                if (isset($row['id'])) {
                    $query->where('id', '<>', $row['id']);
                }
            })->find();
            if ($exist) {
                throw new Exception("已经存在同等级VIP");
            }
            $pricedata = (array)json_decode($row['pricedata'], true);
            if (!$pricedata) {
                throw new Exception("价格配置不能为空");
            }
        });
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden'), 'pulloff' => __('Pulloff')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }

    /**
     * 获取VIP名称自定义颜色
     * @return array
     */
    public static function getCustomColor()
    {
        $colorArr = [
            'default',
            'primary',
            'success',
            'warning',
            'danger',
            'info'
        ];
        $vipList = self::where('id', '>', 0)->column('name,id');
        foreach ($vipList as $index => &$item) {
            $item = $colorArr[$item % 6];
        }
        return $vipList;
    }


}

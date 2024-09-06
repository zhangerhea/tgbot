<?php

namespace addons\leescore\controller;

use think\addons\Controller;
use think\Db;


/**
 * 积分商城
 * By:龙组的赵日天
 * Time: 2022.6.23
 * Version: v1.0.0
 */
class Upgrade extends Controller
{
    public function _initialize()
    {
        parent::_initialize();
        if (!$this->request->isCli()) {
            die('请使用命令行进行升级。');
        }
    }

    public function upgrade()
    {
        $is_exist = Db()->query("show columns from " . config('database.prefix') . "leescore_address like 'truename'");
        if ($is_exist) {
            die('你的插件数据库不需要升级');
        }

        Db::startTrans();
        try {
            // 创建新增数据表
            $add_table = $this->addTable();
            // 创建字段
            $add_Fields = $this->addFields();
            // 数据迁移
            $data_move = $this->dataMove();
            // 修改字段类型
            $update_fields_type = $this->updateFieldsType();
            // 删除字段
            $delete_fields = $this->deleteFields();
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            dump("升级失败：" . $e->getMessage());
            exit;
        }
        dump("数据库升级成功");
        return;
    }

    /**
     * 添加数据表
     */
    private function addTable()
    {
        try {
            $this->execSql("add_table.sql");
            return true;
        } catch (\Exception $e) {
            dump($e->getMessage());
        }
    }

    /**
     * 添加字段
     */
    private function addFields()
    {
        try {
            $this->execSql("add_fields.sql");
            return true;
        } catch (\Exception $e) {
            dump($e->getMessage());
        }
    }

    /**
     * 数据迁移
     */
    private function dataMove()
    {
        // 将firstname和lastname合并为truename字段。
        $is_exist_fields_firstname = Db()->query('show columns from ' . config('database.prefix') . 'leescore_address like "firstname"');
        $is_exist_fields_lastname = Db()->query('show columns from ' . config('database.prefix') . 'leescore_address like "lastname"');
        if (!$is_exist_fields_firstname || !$is_exist_fields_lastname) {
            exception('无需升级');
        }

        $address = Db::name("leescore_address")->field("id,firstname,lastname")->select();
        if ($address) {
            foreach ($address as $k => $v) {
                Db::name('leescore_address')->where("id", $v['id'])->update(['truename' => ($v['firstname'] . $v['lastname'])]);
            }
        }

        $is_exist_address_id = Db()->query('show columns from ' . config('database.prefix') . 'leescore_order like "address_id"');

        if (!$is_exist_address_id) {
            exception("数据库结构不完整");
        }
        // 订单商品表绑定userid
        $row = Db::name('leescore_order_goods')
            ->alias("a")
            ->join('__LEESCORE_ORDER__ b', 'b.id = a.order_id', 'inner')
            ->field("b.uid,a.id,a.order_id, a.createtime")
            ->select();
        foreach ($row as $key => $val) {
            Db::name('leescore_order_goods')->where("id", $val['id'])->setField("userid", $val['uid']);
        }

        $res = Db::name('leescore_order')
            ->alias("a")
            ->join('__LEESCORE_ADDRESS__ b', 'b.id = a.address_id', 'inner')
            ->field("b.*,a.address_id as addid, a.createtime,a.status, a.id as order_id")
            ->where('a.status', 'gt', 0)
            ->select();
        foreach ($res as $ks => $vs) {
            $insert = [
                'order_id'   => $vs['order_id'],
                'zip'        => $vs['zip'],
                'mobile'     => $vs['mobile'],
                'country'    => $vs['country'],
                'region'     => $vs['region'],
                'city'       => $vs['city'],
                'xian'       => $vs['xian'],
                'address'    => $vs['address'],
                'createtime' => $vs['createtime'],
                'truename'   => $vs['firstname'] . $vs['lastname'],
            ];

            // 收货地址重新记录(修复原有设计缺陷)
            Db::name('leescore_order_address')->insert($insert);
        }

        return true;
    }

    /**
     * 更新字段类型
     */
    private function updateFieldsType()
    {
        try {
            $this->execSql("update_fields_types.sql");
            return true;
        } catch (\Exception $e) {
            dump($e->getMessage());
        }
    }

    /**
     * 删除字段
     */
    private function deleteFields()
    {
        try {
            $this->execSql("delete_fields.sql");
            return true;
        } catch (\Exception $e) {
            dump($e->getMessage());
        }
    }


    /**
     * SQL脚本处理
     * @param $file
     * @return string
     */
    private function getSql($file)
    {
        if (is_file($file)) {
            $sql = file_get_contents($file);
            $arr = ['\r\n', '\r', '\n'];
            $sql = str_replace(PHP_EOL, "", $sql);
            $sql = str_replace("__PREFIX__", config('Database.prefix'), $sql);
            return $sql;
        }
        return '';
    }

    /**
     * 执行SQL
     * @param $name
     * @return bool
     */
    private function execSql($name)
    {
        $sqlFile = ROOT_PATH . "addons" . DS . "leescore" . DS . "upgrade" . DS . "sql" . DS . $name;
        if (is_file($sqlFile)) {
            $lines = file($sqlFile);
            $templine = '';
            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*') {
                    continue;
                }

                $templine .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    $templine = str_ireplace('__PREFIX__', config('database.prefix'), $templine);
                    $templine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $templine);
                    try {
                        Db::getPdo()->exec($templine);
                    } catch (\PDOException $e) {
                        //$e->getMessage();
                    }
                    $templine = '';
                }
            }
        }
        return true;
    }
}

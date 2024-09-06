<?php

namespace addons\leescore\controller;

use think\Addons;
use think\Db;
use think\Config;
use think\Request;
use think\Exception;
use addons\leescore\controller\ReturnCode;
use addons\leescore\model\LinkCategory;

/**
 * 积分商城基类
 * By:龙组的赵日天
 * Time: 2018-12-14
 * Version: v1.1.0
 */
class Base extends \think\addons\Controller
{

    public function _initialize()
    {
        parent::_initialize();
        $this->loadGlobalsConfig();
        $this->getLinks('links', '2'); // 友情链接
        $this->getLinks('footer', '1');  // 底部菜单
    }

    /**
     * 获取指定类型商品
     * @param $status int 商品状态:0=回收站,1=仓库中,2=上架, false=全部商品
     * @param $flags  string 推荐类型: hot=热门商品,recommend=掌柜推荐, index=置顶推荐, new=最新上架
     * @param $order  string 排序： id desc
     * @param $limit  int 返回记录数
     * @return $data array 结果集
     */
    protected function getGoodsData($status = 2, $flags = false, $order = 'weigh asc', $limit = 8)
    {
        $status !== false && $where['status'] = $status;
        //开始展示时间
        $where['firsttime'] = ['elt', time()];
        //结束展示时间
        $where['lasttime'] = ['egt', time()];
        if ($flags !== false) {
            $where[] = array('exp', Db::raw("FIND_IN_SET('" . $flags . "',`flag`)"));
        }
        $goodsModel = model('addons\leescore\model\Goods');
        // 缓存两分钟
        return $goodsModel->where($where)->order($order)->cache(120)->limit($limit)->select();
    }

    /**
     * 获取菜单(2级) + 分类数据
     * @param int  $is_mobile 显示类型: 1=仅移动端
     * @param int  $limit     获取记录数
     * @param bool $is_data   是否携带数据
     * @return array $data 查询结果
     */
    protected function getCateList($limit = 15, $is_mobile = '', $is_data = false)
    {
        $cate_model = new \addons\leescore\model\Category();
        $navs = $cate_model->getNavandGoods($is_mobile, $limit, $is_data);
        // dump($navs);
        return $navs;
    }


    /**
     * 链接分类与数据获取
     * param1 string 映射变量名
     * param2 array 结果集 (分类与分类下所有内容)
     * view array 映射结果集至模板
     * return array $data
     */
    protected function getLinks($cname = 'links', $type = '2')
    {
        $model_link_category = new LinkCategory();
        $links = $model_link_category->getLinkData($type);
        $this->view->assign($cname, $links);
        return $links;
    }

    /**
     * 碎片获取器
     * param1 $ids array 要获取的碎片ID
     * return array $data 返回数据集, 如果参数为空返回false
     */
    protected function getSuipian($ids)
    {
        if (is_array($ids)) {
            $ids = implode(",", $ids);
            $data = Db::name('leescore_link')->where("id", 'in', $ids)->select();
        } else {
            if (empty($ids)) return false;
            $data = Db::name('leescore_link')->where("id", 'in', $ids)->find();
        }
        return $data;
    }

    protected function msgSuccess($data = '', $msg = '操作成功', $code = ReturnCode::SUCCESS)
    {
        $status = 1;
        $return = [
            'code'   => $code,
            'msg'    => $msg,
            'data'   => $data,
            'status' => $status
        ];

        return $return;
    }

    protected function msgFailed($code = ReturnCode::FAILED, $msg = '操作失败', $data = '', $show_str = false)
    {
        $return = [
            'code'   => $code,
            'msg'    => $msg,
            'data'   => $data,
            'status' => 0
        ];
        $show_str == true && $return = "错误提示({$code}): {$msg}";

        return $return;
    }

    protected function loadGlobalsConfig()
    {
        //获取插件配置信息
        $config = get_addon_config($this->addon);

        //是否开启订单清理
        if ((int)$config['open_clear'] == 1) {
            if (!is_dir(LOG_PATH)) {
                mkdir(LOG_PATH);
            }

            //判断日志目录是否存在
            if (!file_exists(LOG_PATH . "leescorelogs" . DS . "clear_log.log")) {
                //文件目录不存在，创建目录
                if (!is_dir(LOG_PATH . "leescorelogs" . DS)) {
                    mkdir(LOG_PATH . "leescorelogs" . DS);
                }
                $f = fopen(LOG_PATH . "leescorelogs" . DS . "clear_log.log", "w+") or die(LOG_PATH . "。缺少写入权限。");
                fclose($f);
            }

            //读取文件内容（里面只有时间戳）
            $val = file_get_contents(LOG_PATH . "leescorelogs" . DS . "clear_log.log");
            $open_time = intval($config['open_clear_time']);

            //下次执行时间
            $times = intval($val) + ($open_time * 60);

            //上次执行时间
            if ($val == '' || $times < time()) {
                Db::startTrans();
                try {
                    //创建订单，但是没有走到付款步骤的订单
                    $cleTime = time() - (intval($config['order_out_time']) * 60);
                    $www['createtime'] = ['elt', $cleTime];
                    $www['auth_clear_level'] = 0;
                    $row = Db::name('leescore_order')->where($www)->select();
                    if ($row) {

                        //关闭已创建但未选择地址的订单。
                        foreach ($row as $k => $v) {
                            $order_goods = Db::name("leescore_order_goods")->where("order_id", $v['id'])->select();
                            if ($order_goods) {
                                // 恢复库存
                                foreach ($order_goods as $key => $val) {
                                    Db::name("leescore_goods")->where("id", $val['goods_id'])->setInc('stock', $val['buy_num']);
                                }
                            } else {
                                Db::name("leescore_goods")->where("id", $v['goods_id'])->setInc('stock', $val['buy_num']);
                            }
                            $del['order_id'] = $v['id'];
                            Db::name('leescore_order')->where("id", $v['id'])->update(['status' => '-1']); // 关闭
                        }
                    }
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                }

                //记录当前执行时间
                $file = fopen(LOG_PATH . "leescorelogs" . DS . "clear_log.log", "w+");
                fwrite($file, time());
                fclose($file);
            }
        }//订单清理

        Config::set("addonConfig", $config);
    }
}

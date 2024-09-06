<?php

namespace app\api\controller;

use think\Db;

use addons\leescore\model\Address;
use addons\leescore\model\OrderAddress;
use app\common\controller\Api;
/**
 * 积分商城订单类
 * By:龙组的赵日天
 * Time: 2018-12-14
 * Version: v1.1.0
 */
class Order extends Api
{

    protected $model = null;
    protected $member = null;
    protected $goods = null;
    protected $layout = 'user-layout';
    protected $relationSearch = true;
    protected $cart = null;
    protected $order_goods = null;

    public function _initialize()
    {
        parent::_initialize();

        if (!$this->auth->isLogin()) {
            $this->redirect("index/user/login");
        }
        $this->goods = model('addons\leescore\model\Goods');
        $this->member = $this->auth->getUserInfo();
        $this->cart = model('addons\leescore\model\Cart');
        $this->order_goods = model('addons\leescore\model\OrderGoods');
        $this->model = model('addons\leescore\model\Order');
    }

    // 订单管理列表
    public function index()
    {
        //当前会员的订单
        $w['uid'] = $this->auth->id;
        //正常订单
        $w['isdel'] = array('neq', 1);
        // $listType != '' & $w['status'] = $listType;
        $listType = input('?get.listType') && !empty(input('?get.listType')) ? input('get.listType') : '';
        if ($listType != '') $w['status'] = $listType;
        $keywords = '';
        if ($this->request->isPost()) {
            $keywords = trim(input('post.keywords'));
            $w['order_id'] = array('like', "%$keywords%");
        }
        $config = get_addon_config($this->addon);
        $order_clear_time = $config['order_out_time'];
        $this->assign('order_clear_time', $order_clear_time);
        $list = $this->model->getOrderList($w, input('get.'));
        $this->view->assign('list', $list);
        $this->view->assign('keywords', $keywords);
        return $this->fetch();
    }


    //AJAX分页
    public function getList()
    {
        //排序
        $field = input("get.field");
        $sort = input('get.sort');

        //推荐类型
        (input('?get.flag') && !empty(trim(input('get.flag')))) && $w['flag'] = input('get.flag');

        //商品类型  虚拟商品-实物商品
        (input('?get.type') && !empty(trim(input('get.type')))) && $w['type'] = input('get.type');

        //积分查询
        $score_start = (input('?get.score-start') && empty(trim(input('get.score-start')))) ? input("get.score-start") : false;
        $score_end = (input('?get.score-end') && empty(trim(input('get.score-end')))) ? input("get.score-end") : false;

        if ($score_start && $score_end) {
            $w['score'] = ['between', [$score_start, $score_end]];
        }

        //上架中的商品  0=删除，2=上架中，1=仓库中
        $w['status'] = 2;

        //仅显示积分兑换模式下的商品
        //$w['paytype'] = 0;

        //所属该用户的订单
        $w['uid'] = $this->auth->id;

        $page = request()->param('page');

        $order = request()->param('field') . " " . request()->param('sort');

        $list = $this->model->where($w)->order($order)->paginate(15, false,
            [
                'path'     => 'javascript:ajaxPage([PAGE]);',
                'page'     => $page,
                'var_page' => 'page',
            ]
        );

        $html = '';
        foreach ($list as $key => $vo) {
            $html .= "<div class=\"col-xs-6 col-md-4\"><div class=\"thumbnail radius-none\"><a href=\"javascript:;\"><img style=\"width:100%; height:180px;\" class=\"img-responsive\" src=\"{$vo['thumb']}\" alt=\"{$vo['name']}\"></a><div class=\"\"><h4><a class=\"text-danger\" href=\"javascript:;\">{$vo['name']}</a></h4><div class=\"col-xs-6 col-md-6 padding-none\"><h5 class=\"text-muted \">" . __('score') . ": <strong>{$vo['scoreprice']}</strong> " . "</h5><h5 class=\"text-muted\">" . __('stock') . ": {$vo['stock']}</h5></div><div class=\"col-xs-6 col-md-6\"><p class=\"padding-top\"><a class=\"btn btn-warning btn-flat\" href=\"\">" . __('buy') . "</a></p></div></div><div class=\"clearfix\"></div></div></div>";
        }

        $page = $list->render();
        return json(['list' => $html, 'page' => $page]);
    }

    public function details()
    {
        $id = (input('?get.id') && input('get.id') != '') ? input('get.id') : false;
        if (!$id) {
            $this->error(__('order detail param error'));
        }

        $w['isdel'] = ['neq', 1];
        $w['uid'] = $this->auth->id;
        $w['id'] = $id;
        $detail = $this->model->getOrderDetail($w, $id);
        //dump($detail);
        $this->view->engine->layout(false);
        $this->view->assign('vo', $detail);
        return $this->fetch();
    }

    //删除
    public function delete()
    {
        $ids = $this->request->param()['ids'];
        try {
            $w['id'] = array('in', $ids);
            $w['uid'] = $this->auth->id;
            $this->model->where($w)->update(['isdel' => 1]);
            $result = ['status' => true, 'msg' => 'Success'];

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        return json($result);
    }

    //单条订单删除
    public function delone()
    {
        $id = input('get.id') ? input('get.id') : false;
        if (!$id) {
            return json(['status' => false, 'msg' => 'ID参数不能为空']);
        }

        try {
            $w['id'] = $id;
            $w['uid'] = $this->auth->id;
            $this->model->where($w)->update(['isdel' => 1]);
            $res = ['status' => true, 'msg' => 'success'];

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        return json($res);
    }

    //关闭订单
    public function closeOrder()
    {
        $id = input('get.id') ? input('get.id') : false;
        if (!$id) {
            return json(['status' => false, 'msg' => 'ID参数不能为空']);
        }

        try {
            $w['id'] = $id;
            $w['uid'] = $this->member['id'];
            $this->model->where($w)->update(['status' => '-1']);
            return json(['status' => true, 'msg' => 'success']);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function orderSign()
    {
        if ($this->request->isPost()) {
            $id = input('post.id');

            try {
                $w['id'] = $id;
                $w['uid'] = $this->member['id'];
                $this->model->where($w)->update(['status' => 3, 'virtual_sign_time' => time()]);
                return true;
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }
    }

    /**
     *
     * 购买前检测库存
     *
     */

    public function buyCheck()
    {

        $id = (int)input('get.id');
        $number = (int)input('get.number');
        //积分验证
        $info = $this->goods->where("id", $id)->find();
        $score_total = $info['scoreprice'] * $number;
        if ($this->auth->score < $score_total) {
            $result = ['code' => false, 'msg' => __("min score")];
            return $result;
        }

        //库存验证
        if ($info['stock'] < 1) {
            $result = ['code' => false, 'msg' => __("min stock")];
            return $result;
        }

        //最大购买值
        if ($info['max_buy_number'] != '-1') {
            $map['uid'] = $this->auth->id;
            $map['goods_id'] = $info['id'];
            $maxNum = Db::name('leescore_exchangelog')->where($map)->count();
            if ($maxNum >= $info['max_buy_number']) {
                $result = ['code' => false, 'msg' => __("ex change max")];
                return $result;
            }
        }

        $result = ['code' => true, 'msg' => "Success"];
        return $result;
    }


    /**
     * @param $id     商品ID
     * @param $number 购买数量
     *                return array;
     */

    protected function isCheck($id, $number)
    {
        //积分验证
        $info = $this->goods->where("id", $id)->find();
        $score_total = $info['scoreprice'] * $number;

        if ($this->auth->score < $score_total) {
            $result = ['code' => false, 'msg' => __("min score")];
            return $result;
        }

        // 余额验证
        if ($info['money'] > 0) {
            $money_total = $info['money'] * $number;
            if ($this->auth->money < $money_total) {
                $result = ['code' => false, 'msg' => __("余额不足")];
                return $result;
            }
        }

        //库存验证
        if ($info['stock'] < $number) {
            $result = ['code' => false, 'msg' => __("min stock")];
            return $result;
        }

        //最大购买值
        if ($info['max_buy_number'] != '-1') {
            $map['og.userid'] = $this->auth->id;
            $map['og.goods_id'] = $info['id'];
            $map['o.status'] = ['not in', '-1,-2'];
            $maxNum = Db::name('leescore_order_goods')
                ->alias('og')
                ->join('__LEESCORE_ORDER__ o', 'og.order_id = o.id')
                ->where($map)
                ->field('og.*, o.status')
                ->sum("og.buy_num");
            // $maxNum = Db::name('leescore_order_goods')->where($map)->count("buy_num");
            if ($maxNum >= $info['max_buy_number'] || ($maxNum + $number) > $info['max_buy_number']) {
                $result = ['code' => false, 'msg' => __("ex change max")];
                return $result;
            }
        }

        $result = ['code' => true, 'msg' => "Success"];
        return $result;
    }


    //创建订单
    public function createOrderOne()
    {

        //读取插件配置（用于获取表前缀）
        $add_config = get_addon_config('leescore');
        //商品ID
        $id = input("post.id");

        //商品数量
        $number = intval(input('post.number'));
        //dump(input("post."));
        $result = $this->isCheck($id, $number);
        //验证不通过
        if ($result['code'] != true) {
            $this->error($result['msg']);
        }

        //获取商品信息
        $goodsDetail = $this->goods->getGoodsDetail($id);

        //积分
        $scoreTotal = (int)($goodsDetail['scoreprice'] * $number);
        //货币
        $moneyTotal = round(($goodsDetail['money'] * $number), 2);

        //用户编号
        $data['uid'] = $this->auth->id;
        //生成订单号  表前缀 + 时间戳10位 + 微秒3位 + 随机数4位
        $sn = ucfirst(trim($add_config['order_prefix'])) . sprintf("%010d", (time() - 946656000)) . sprintf("%03d", (float)microtime() * 1000) . mt_rand(1000, 9999);
        $data['order_id'] = $sn;
        //收货地址
        //$data['address_id'] = '';
        //支付状态 0未付款 1已付款 2已退款
        $data['pay'] = 0;
        //订单状态： -2=驳回, -1=取消订单,0=未支付,1=已支付,2=已发货,3=已签收,4=退货中,5=已退款
        $data['status'] = 0;
        //支付时间
        //$data['paytime'] = time();
        //付款方式，score = 积分付款, weixin = 微信支付 , alipay = 支付宝 , paypal = paypal
        //$data['paytype'] = '';
        //删除订单（软删除）
        $data['isdel'] = 0;
        //订单创建时间
        $data['createtime'] = time();
        //需支付总积分
        $data['score_total'] = $scoreTotal;
        //需支付总款
        $data['money_total'] = $moneyTotal;

        Db::startTrans();
        try {
            //创建订单
            $this->model->insert($data);
            $lastid = $this->model->getLastInsID();
            $goods_data['order_id'] = $lastid;
            $goods_data['goods_id'] = $goodsDetail['id'];
            $goods_data['buy_num'] = $number;
            $goods_data['score'] = $goodsDetail['scoreprice'];
            $goods_data['money'] = $goodsDetail['money'];
            $goods_data['goods_name'] = $goodsDetail['name'];
            $goods_data['goods_thumb'] = $goodsDetail['thumb'];
            $goods_data['createtime'] = time();
            $goods_data['userid'] = $this->auth->id;

            //写入订单商品表
            $this->order_goods->insert($goods_data);

            $dec['id'] = $goodsDetail['id'];
            //减少库存
            $this->goods->where($dec)->setDec('stock', $number);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }

        $this->redirect(addon_url('leescore/order/postOrders', ['orderid' => $lastid]));
    }

    //创建订单（此方法用于创建由购物车提交上来的多商品订单）
    public function createOrder()
    {
        //读取插件配置（用于获取表前缀）
        $add_config = get_addon_config('leescore');
        //购物车表编号
        $ids = $this->request->param()['ids'];
        //积分
        $scoreTotal = 0;
        //货币
        $moneyTotal = 0;
        foreach ($ids as $key => $val) {
            //获取商品信息
            $info = $this->cart
                ->alias('cart')
                ->join('__LEESCORE_GOODS__ goods', 'goods.id = cart.goods_id')
                ->field('cart.*,goods.id,goods.scoreprice,goods.money,goods.thumb,goods.paytype,goods.status,goods.type,goods.name')
                ->where("cart.id", $val)->find();

            //购买数量
            $number = (int)$this->request->param("number_" . $val);
            $scoreTotal = $scoreTotal + ($info['scoreprice'] * $number);
            $moneyTotal = $moneyTotal + (float)($info['money'] * $number);
            $isBuy = $this->isCheck($info['goods_id'], $number);
            if ($isBuy['code'] === false) {
                $this->error($info['name'] . ">>>" . $isBuy['msg']);
            }

        }


        //用户编号
        $data['uid'] = $this->auth->id;
        //生成订单号  表前缀 + 时间戳10位 + 微秒3位 + 随机数4位
        $sn = ucfirst(trim($add_config['order_prefix'])) . sprintf("%010d", (time() - 946656000)) . sprintf("%03d", (float)microtime() * 1000) . mt_rand(1000, 9999);
        $data['order_id'] = $sn;
        //收货地址
        //$data['address_id'] = '';
        //支付状态 0未付款 1已付款 2已退款
        $data['pay'] = 0;
        //订单状态： -2=驳回, -1=取消订单,0=未支付,1=已支付,2=已发货,3=已签收,4=退货中,5=已退款
        $data['status'] = 0;
        //支付时间
        //$data['paytime'] = time();
        //付款方式，score = 积分付款, weixin = 微信支付 , alipay = 支付宝 , paypal = paypal
        //$data['paytype'] = '';
        //删除订单（软删除）
        $data['isdel'] = 0;
        //订单创建时间
        $data['createtime'] = time();
        //需支付总积分
        $data['score_total'] = $scoreTotal;
        //需支付总款
        $data['money_total'] = $moneyTotal;
        //自动清理订
        $data['auth_clear_level'] = 0;

        //创建订单
        $this->model->insert($data);
        $lastid = $this->model->getLastInsID();

        foreach ($ids as $k => $v) {
            $numbers = (int)$this->request->param("number_" . $v);
            //从购物车中获取商品信息
            $info = $this->cart->with('goodsDetail')->where("id", $v)->find();
            $order_goods['order_id'] = $lastid;
            $order_goods['goods_id'] = $info->goods_id;
            $order_goods['buy_num'] = $numbers;
            $order_goods['score'] = $info->goods_detail->scoreprice;
            $order_goods['money'] = $info->goods_detail->money;
            $order_goods['goods_name'] = $info->goods_detail->name;
            $order_goods['goods_thumb'] = $info->goods_detail->thumb;
            $order_goods['createtime'] = time();
            $order_goods['userid'] = $this->auth->id;
            // 减少商品库存
            $this->goods->where("id", $info->goods_id)->setDec('stock', $numbers);
            $this->order_goods->insert($order_goods);

        }

        $this->redirect(addon_url('leescore/order/postOrders', ['orderid' => $lastid]));
    }

    protected function checkOrderStatus($orderid = 0)
    {
        $config = get_addon_config($this->addon);
        $order = $this->model->where('id', $orderid)->find();
        if ($order['status'] < 0) {
            return false;
        }

        // 订单已过期, 但没有被自动关闭
        if (intval($order['status'] == 0)) {
            $outTime = $order['createtime'] + (intval($config['order_out_time']) * 60);
            if (time() > $outTime) {
                $this->model->where("id", $orderid)->update(['status' => '-1']);
                return false;
            }
            return true;
        }
    }

    //商品清单确认
    public function postOrders()
    {
        $orderid = $this->request->param("orderid");
        $w['uid'] = $this->auth->id;
        $w['isdel'] = 0;
        if (!$this->checkOrderStatus($orderid)) {
            $this->error("该订单不存在或已经关闭~", addon_url('leescore/order/index'));
        }
        //列出10个收货地址
        $address = Db::name('leescore_address')->where($w)->order('status desc')->limit(10)->select();

        $order = $this->model
            ->with("orderGoods")
            ->where("id", $orderid)
            ->find();

        if (!$order) {
            $this->error(__('select this a order'));
        } else {
            if ($order['status'] != 0) {
                $this->error(__('not rebuy'));
            }
        }
        $config = get_addon_config($this->addon);
        $order_clear_time = $order['createtime'] + (intval($config['order_out_time']) * 60);
        $this->assign('address', $address);
        $this->assign('order_clear_time', $order_clear_time);
        $this->assign('item', $order);
        return $this->view->fetch("postorders");
    }


    //开始支付
    public function pay()
    {
        //订单ID
        $oid = input('post.oid');
        //订单备注
        $other = trim(input('post.other'));
        //用户收货地址
        $address_id = input("post.address");

        $order = $this->model->where("id", $oid)->find();

        if (!$order) {
            $this->error(__('select this a order'));
        }

        //需支付积分
        $scoreTotal = $order['score_total'];

        if ($this->auth->score < $order['score_total']) {
            return json(['code' => false, 'msg' => __('min score')]);
        }

        //需付款数额
        $moneyTotal = $order['money_total'];

        //检查账户余额
        if ($moneyTotal > $this->auth->money) {
            return json(['code' => false, 'msg' => __('您的账户余额不足。')]);
        }

        //支付方式
        $paytypes = $moneyTotal > 0 ? 'money' : 'score';

        //插件配置
        $add_config = get_addon_config('leescore');

        // 订单地址处理
        $address_model = new Address();
        $addressInfo = $address_model->get($address_id);

        $order_data = [
            'zip'      => $addressInfo['zip'],
            'order_id' => $order['id'],
            'mobile'   => $addressInfo['mobile'],
            'country'  => $addressInfo['country'],
            'region'   => $addressInfo['region'],
            'city'     => $addressInfo['city'],
            'xian'     => $addressInfo['xian'],
            'address'  => $addressInfo['address'],
            'truename' => $addressInfo['truename'],
        ];

        $order_address = new OrderAddress();
        $addreid = $order_address->insert($order_data);
        //收货地址
        //$data['address_id'] = $address;
        //支付状态 0未付款 1已付款 2已退款
        //付款方式，score = 积分付款, weixin = 微信支付 , alipay = 支付宝 , paypal = paypal
        $data['paytype'] = $paytypes;
        //用户备注
        $data['other'] = $other;
        //需支付总积分
        $data['score_total'] = $scoreTotal;
        //需支付总款
        $data['money_total'] = $moneyTotal;
        $data['auth_clear_level'] = 2;

        $map['id'] = $oid;
        $order = $this->model->where($map)->find();
        if ($order['status'] != 0) {
            $this->error(__('not rebuy'));
        }

        $this->model->where($map)->update($data);
        //如果结算金额为0,这个商品则为积分商品。
        if ($moneyTotal <= 0) {
            $order_goods = $this->order_goods->where('order_id', $oid)->column('id');
            $goodsInfo = $this->goods->where('id', 'in', $order_goods)->select();
            // var_dump($goodsInfo);
            //仅有积分商品，直接操作库存，启动事务,确保数据一致性。
            Db::startTrans();
            try {
                foreach($goodsInfo as $k => $v){
                    //购买记录
                    $log = ['uid' => $this->auth->id, 'goods_id' => $v->id, 'order_id' => $this->model->getLastInsID(), 'createtime' => time(), 'ip' => $this->request->ip()];
                    Db::name('leescore_exchangelog')->insert($log);
                }
                
                //写入积分日志
                \app\common\model\User::score(-$scoreTotal, $this->auth->id, '消费积分兑换商品');

                $orderData = [
                    'pay'         => 1,  //已支付
                    'status'      => 1,
                    'paytime'     => time(),
                    'trade_score' => $scoreTotal,
                    'trade_money' => 0,
                    'paytype'     => 'score'
                ];
                //扣除积分后，修改支付状态
                $this->model->where('id', $oid)->update($orderData);
                //从购物车中移除
                $order_goods = Db::name('leescore_order_goods')->where('order_id', $order['id'])->select();
                if ($order_goods) {
                    foreach ($order_goods as $key => $val) {
                        $cart = $this->cart->where('goods_id', $val['goods_id'])->find();
                        //$cart = $this->cart::get("goods_id", $val['goods_id']);
                        if ($cart) {
                            $cart->delete();
                        }
                        $this->goods->where("id", $val['goods_id'])->setInc("usenum", $val['buy_num']);
                    }
                }
                $arr = ['code' => true, 'msg' => __('gift order success')];
                Db::commit();

            } catch (\Exception $e) {
                $arr = ['code' => false, 'msg' => __('pay faild')];
                //数据同步写入或修改时发生错误，回滚数据至修改前
                Db::rollback();
            }

            if ($arr['code'] == true) {
                $this->success($arr['msg'], addon_url('leescore/order/index'));
            } else {
                $this->error($arr['msg']);
            }
        } else {
            //商品信息
            $goodsInfo = $this->goods->where($map)->find();
            //仅有积分商品，直接操作库存，启动事务,确保数据一致性。
            Db::startTrans();
            try {
                //购买记录
                $log = ['uid' => $this->auth->id, 'goods_id' => $goodsInfo['id'], 'order_id' => $this->model->getLastInsID(), 'createtime' => time(), 'ip' => $this->request->ip()];
                Db::name('leescore_exchangelog')->insert($log);
                //写入积分日志
                \app\common\model\User::score(-$scoreTotal, $this->auth->id, '消费积分兑换商品');

                //扣除余额
                \app\common\model\User::money(-$moneyTotal, $this->auth->id, '兑换商品余额扣除');
                $orderData = [
                    'pay'         => 1,
                    'status'      => 1,
                    'paytime'     => time(),
                    'trade_score' => $scoreTotal, //实际支付积分
                    'trade_money' => $moneyTotal, //实际支付金额
                    'paytype'     => 'money'
                ];
                //扣除金额、积分后，修改支付状态
                $this->model->where('id', $oid)->update($orderData);

                //如果此商品在购物车中存在，就从购物车中移除。因为已经购买成功了。
                $order_goods = Db::name('leescore_order_goods')->where('order_id', $oid)->select();

                if ($order_goods) {
                    foreach ($order_goods as $key => $val) {
                        $cart = $this->cart->where('goods_id', $val['goods_id'])->find();
                        //$cart = $this->cart::get("goods_id", $val['goods_id']);
                        if ($cart) {
                            $cart->delete();
                        }
                        $this->goods->where('id', $val['goods_id'])->setInc("usenum", $val['buy_num']);
                    }
                }
                $arr = ['code' => true, 'msg' => __('gift order success')];
                Db::commit();
            } catch (\Exception $e) {
                //数据同步写入或修改时发生错误，回滚数据至修改前
                $arr = ['code' => false, 'msg' => __('pay faild')];
                Db::rollback();
            }

            if ($arr['code'] == true) {
                $this->success($arr['msg'], addon_url('leescore/order/index'));
            } else {
                $this->error($arr['msg']);
            }
        }
    }
}

<?php
namespace app\api\controller;


use think\Db;
use think\Config;
use addons\leescore\model\Goods;
use app\common\controller\Api;
/**
 * 积分商城购物车管理
 * By:龙组的赵日天
 * Time: 2021.3.30
 * Version: v1.1.6
 */
class Cart extends Api
{
    protected $relationSearch = true;
    protected $model = null;
    protected $member = null;
    protected $cart = null;
    protected $layout = 'default';

    public function _initialize()
    {
        parent::_initialize();

        if (!$this->auth->isLogin()) {
            return json_encode($this->msgFailed('', '您还没有登录', ReturnCode::NO_SIGN));
        }

        $this->member = $this->auth->getUserInfo();
        $this->model = model('addons\leescore\model\Cart');
    }

    //购物车列表
    public function index()
    {

    }

    public function add()
    {
        $id = $this->request->param('id');
        $w['goods_id'] = $id;
        $w['uid'] = $this->auth->id;
        $re = Db::name('leescore_cart')->where($w)->find();
        if ($re) {

            Db::name('leescore_cart')->where($w)->setInc('number', 1);
            return json(['status' => true, 'msg' => 'success']);
        }
        $data['uid'] = $this->auth->id;
        $data['goods_id'] = $id;
        $data['number'] = 1;
        $data['createtime'] = time();
        if ($this->model->insert($data)) {
            return json(['status' => true, 'msg' => 'success']);
        } else {
            return json(['status' => false, 'msg' => 'faild']);
        }
    }

    //删除
    public function delete()
    {
        $ids = $this->request->param()['ids'];
        try {
            $w['id'] = array('in', $ids);
            $w['uid'] = $this->member['id'];
            $this->model->where($w)->delete();
            $res = json(['status' => true, 'msg' => 'Success']);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        return $res;
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
            $w['uid'] = $this->member['id'];
            $this->model->where($w)->update(['isdel' => 1]);
            return json(['status' => true, 'msg' => 'success']);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }


    public function getUserCartTotal()
    {
        $userid = $this->member['id'];
        $total = $this->model->getUserCartTotal($userid);
        return $total;
    }

    // 增加数量
    public function incGoodsNumber()
    {
        $id = input("post.gid");
        $num = input('post.numbers');
        $isCheck = $this->isGoodsNum($id, $num);
        if ($isCheck) {
            return
                $res = json(['status' => true, 'msg' => 'success', 'num' => $num + 1]);
        } else {
            $res = json(['status' => false, 'msg' => $this->msgFailed(ReturnCode::MAX_NUMBER, '商品库存不足', [], true)]);
        }
        return $res;
    }

    /**
     * 检测商品是否有库存
     * 检测商品是否达到该用户的最大购买数量
     */
    protected function isGoodsNum($id = false, $num = 0)
    {

        if ($id === false) return false;

        $goods_model = model('addons\leescore\model\Goods');
        $goods = $goods_model->where("id", $id)->find();
        if ($goods['stock'] > $num) {
            if ($goods['max_buy_number'] != '-1') {
                $map['og.userid'] = $this->auth->id;
                $map['og.goods_id'] = $goods['id'];
                $map['o.status'] = ['egt', 0];
                $maxNum = Db::name('leescore_order_goods')
                    ->alias('og')
                    ->join('__LEESCORE_ORDER__ o', 'og.order_id = o.id')
                    ->where($map)
                    ->field('og.*, o.status')
                    ->sum("og.buy_num");
                if (($maxNum + $num) < $goods['max_buy_number']) {
                    return true;
                } else {
                    return false;
                }
            } else {
                // 不限制购买数量
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $id     商品ID
     * @param $number 购买数量
     *                return array;
     */
    public function isCheck($id, $number)
    {
        $goods_model = model('addons\leescore\model\Goods');
        //积分验证
        $info = $goods_model->where("id", $id)->find();
        $score_total = $info['scoreprice'] * $number;
        if ($this->auth->score < $score_total) {
            $result = ['code' => false, 'msg' => __("min score")];
            return $result;
        }

        //库存验证
        if ($info['stock'] < $number) {
            $result = ['code' => false, 'msg' => __("min stock")];
            return $result;
        }

        //最大购买值
        if ($info['max_buy_number'] != '-1') {
            $map['og.userid'] = $this->member['id'];
            $map['og.goods_id'] = $info['id'];
            $map['o.status'] = ['egt', 0];
            $maxNum = Db::name('leescore_order_goods')
                ->alias('og')
                ->join('__LEESCORE_ORDER__ o', 'og.order_id = o.id')
                ->where($map)
                ->field('og.*, o.status')
                ->sum("og.buy_num");

            if ($maxNum >= $info['max_buy_number'] || ($maxNum + $number) > $info['max_buy_number']) {
                $result = ['code' => false, 'msg' => __("ex change max")];
                return $result;
            }
        }

        $result = ['code' => true, 'msg' => "Success"];
        return $result;
    }

    // 商品数量检测
    public function checkGoodsNumber()
    {
        $ids = $this->request->param()['ids'];
        //积分
        $scoreTotal = 0;
        //货币
        $moneyTotal = 0;
        $cart_model = model('addons\leescore\model\Cart');
        foreach ($ids as $key => $val) {
            //获取商品信息
            $info = $cart_model
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
                $res = ['code' => false, 'msg' => $info['name'] . ">>>" . $isBuy['msg']];
                return json($res);
            }
        }

        return json(['code' => true, 'msg' => 'ok']);
    }

    public function postCartNumber()
    {
        $id = input('post.cartid');
        $cart = $this->model->getCart($id);
        $number = input('?post.number') ? input('post.number') : 1;
        $ac = input('?post.ac') ? input('post.ac') : 'inc';

        $goodsModel = new Goods();
        $goods = $goodsModel->getGoodsDetail($cart['goods_id']);
        if (!$goods) {
            return $this->msgFailed(ReturnCode::FOUND_DATA, '商品库存不足,无法收藏更多数量');
        }
        if ($goods['stock'] <= $number && $ac == 'inc') {
            return $this->msgFailed(ReturnCode::NO_STOCK, '商品库存不足,无法收藏更多数量');
        }

        if ($goods['status'] != '2') {
            return $this->msgFailed(ReturnCode::DANGER, '非法操作');
        }

        if ($cart['uid'] != $this->auth->id) {
            return $this->msgFailed(ReturnCode::DANGER, '非法操作, 当前登录ID与操作购物车物品不一致');
        }

        if ($goods['max_buy_number'] < $number && $ac == 'inc' && $goods['max_buy_number'] != -1) {
            return $this->msgFailed(ReturnCode::MAX_NUMBER, '您购买的数量已达上限');
        }

        if ($this->model->saveCart($number, $cart['id'])) {
            return $this->msgSuccess('', '', ReturnCode::SUCCESS);
        } else {
            return $this->msgFailed(ReturnCode::DB_SAVE_ERROR);
        }
    }


    public function postCartAdd()
    {
        $goodsId = input('post.id');
        $post_number = (input("?post.num") && !empty(input("post.num"))) ? input("post.num") : 1;
        $goodsModel = new Goods();
        $goods = $goodsModel->getGoodsDetail($goodsId);
        if (!$this->auth->isLogin()) {
            return $this->msgFailed(ReturnCode::NO_SIGN, "你还没有登录");
        }
        if (!$goods) {
            return $this->msgFailed(ReturnCode::FOUND_DATA, "参数不正确，请重试");
        }
        if ($goods['stock'] < $post_number) {
            return $this->msgFailed(ReturnCode::NO_STOCK, '商品库存不足,无法收藏更多数量');
        }

        if ($goods['status'] != '2') {
            return $this->msgFailed(ReturnCode::DANGER, '非法操作');
        }


        $carts = $this->model->getUserCartInfo($goodsId, $this->auth->id);

        $number = $carts ? ($carts['number'] + $post_number) : 1;
        if ($goods['max_buy_number'] < $number && $goods['max_buy_number'] != -1) {
            return $this->msgFailed(ReturnCode::MAX_NUMBER, '您购买的数量已达上限');
        }

        if ($carts) {
            if ($carts['uid'] != $this->auth->id) {
                return $this->msgFailed(ReturnCode::DANGER, '非法操作, 当前登录ID与操作购物车物品不一致');
            }

            $data['number'] = $carts['number'] + $post_number;
            if ($this->model->save($data, ['id' => $carts['id']])) {
                return $this->msgSuccess('', '加入购物车成功~', ReturnCode::SUCCESS);
            } else {
                return $this->msgFailed(ReturnCode::DB_SAVE_ERROR);
            }
        }

        $data['uid'] = $this->auth->id;
        $data['goods_id'] = $goodsId;
        $data['number'] = $post_number;
        $data['createtime'] = time();

        if ($this->model->insert($data)) {
            return $this->msgSuccess('', '加入购物车成功~', ReturnCode::SUCCESS);
        } else {
            return $this->msgFailed(ReturnCode::DB_SAVE_ERROR);
        }
    }

    // 清空购物车
    public function postUserCartDelAll()
    {
        if ($this->model->where(['uid' => $this->auth->id])->delete()) {
            return $this->msgSuccess('', '已为您清空购物车~', ReturnCode::SUCCESS);
        } else {
            return $this->msgFailed(ReturnCode::DB_DELETE_FOUND, '清空失败 :-(');
        }
    }

    public function deleteCartGoods()
    {
        $id = input('post.cartid');
        if ($this->model->where(['id' => $id, 'uid' => $this->auth->id])->delete()) {
            return $this->msgSuccess('', '', ReturnCode::SUCCESS);
        } else {
            return $this->msgFailed(ReturnCode::DB_SAVE_ERROR);
        }
    }

    public function getUserCartList()
    {
        $userid = $this->member['id'];
        $cartList = $this->model->with(['goods'])->where(['uid' => $userid])->limit(0, 7)->select();
        $html = "";

        foreach ($cartList as $k => $v) {
            $html .= '<li class="item"><div class="cart-image-box"><img src="
                    ' .
                $v->goods->thumb
                . '" alt=""></div><div class="goods-cart-box"><small>' .
                $v->goods->name
                . '</small><div class="cart-btn-group"><a href="javascript:;" class="cart-addons">数量: &nbsp;</a><a href="javascript:;" data-action="dec" data-cartid="' .
                $v->id
                . '" class="cart-number-btn cart-addons"><i class="fa fa-minus"></i></a><a href="javascript:;" class="cart-addons cart-number-input">' .
                $v->number
                . '</a><a href="javascript:;" class="cart-number-btn cart-addons" data-cartid="' .
                $v->id
                . '" data-action="inc"><i class="fa fa-plus"></i></a><div class="clearfix"></div></div></div><div class="clearfix"></div></li><div class="clearfix"></div>';
        }

        if (!$cartList) {
            $html = '<li style="color:#2a2a2a;">' . lang('cart empty') . '</li>';
        }

        return $html;
    }
}

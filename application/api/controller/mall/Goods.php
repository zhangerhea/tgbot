<?php
namespace app\api\controller;


use think\Db;
use think\Request;
use addons\leescore\model\OrderGoods;
use addons\leescore\model\Order;
use app\common\controller\Api;
/**
 * 积分商城商品类
 * By:龙组的赵日天
 * Time: 2018-12-14
 * Version: v1.1.0
 */
class Goods extends Api
{

    protected $model = null;
    protected $member = null;
    protected $layout = 'default';
    protected $category = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('addons\leescore\model\Goods');
        $this->category = model('addons\leescore\model\Category');

        // 产品页右侧热销产品
        $usenumdesc = $this->getGoodsData(2, false, "usenum desc", 5);
        $this->view->assign('usenumdesc', $usenumdesc);
    }

    // 产品列表
    public function index()
    {
        $w['topid'] = 0;
        $w['status'] = 'normal';
        $goods_cate = $this->getCateList(99, 1);
        $this->assign("goods_cate", $goods_cate);
        return $this->fetch();
    }

    // 移动端二级联动
    public function getDLevelType()
    {
        $typeid = input("post.typeid");
        $row = $this->category->where(['category_id' => $typeid, 'status' => 'normal'])->select();
        $html = '';
        if ($row) {
            $html = "<select name='cateTypes' class='form-control'><option selected value=''>全部分类</option>";

            foreach ($row as $k => $v) {
                $html .= "<option value='{$v['id']}'>{$v['name']}</option>";
            }

            $html .= "</select>";
        }
        $res = ['status' => 1, 'data' => $html];
        return json($res);
    }

    //AJAX分页
    public function getList()
    {
        //排序
        $field = input("get.field");
        $sort = input('get.sort');
        $keywords = $this->request->param('keywords');
        //dump($keywords);
        //商品类型
        (input('?get.paytype') && !empty(input('get.paytype'))) ? $paytype = trim(input('get.paytype')) : '';

        if (isset($paytype)) {
            //根据积分和价格去识别商品类型。
            switch ($paytype) {
                case 'score':
                    //纯积分商品的货币价格应该为0
                    $w['scoreprice'] = ['gt', 0];
                    $w['money'] = ['elt', 0];
                    break;
                case 'money':
                    //纯货币商品的积分设置应该为0
                    $w['scoreprice'] = ['elt', 0];
                    $w['money'] = ['gt', 0];
                    break;
                case 'sam':
                    //积分+货币的商品 积分及货币价格都不应该为0.
                    $w['scoreprice'] = ['gt', 0];
                    $w['money'] = ['gt', 0];
                default:
                    //默认不筛选类型。
                    break;
            };
        }

        //商品分类
        (input('?get.category') && !empty(input('get.category'))) ? $category = trim(input('get.category')) : '';
        //积分查询
        $score_start = (input('?get.score_start') && !empty(input('get.score_start'))) ? abs((int)input("get.score_start")) : false;
        $score_end = (input('?get.score_end') && !empty(input('get.score_end'))) ? abs((int)input("get.score_end")) : false;

        //价格查询
        $money_start = (input('?get.money_start') && !empty(input('get.money_start'))) ? abs((int)input("get.money_start")) : false;
        $money_end = (input('?get.money_end') && !empty(input('get.money_end'))) ? abs((int)input("get.money_end")) : false;


        //关键词查询
        if (isset($keywords) && !empty($keywords)) {
            $w['name'] = ['like', "%" . $keywords . "%"];
        }

        //积分查询
        if ($score_start && $score_end) {
            $w['scoreprice'] = ['between', [$score_start, $score_end]];
        } else {
            if ($score_start) {
                $w['scoreprice'] = ['egt', $score_start];
            } else {
                if ($score_end) {
                    $w['scoreprice'] = ['elt', $score_end];
                }
            }
        }

        //价格查询
        if ($money_start && $money_end) {
            $w['money'] = ['between', [$money_start, $money_end]];
        } else {
            if ($money_start) {
                $w['money'] = ['egt', $money_start];
            } else {
                if ($money_end) {
                    $w['money'] = ['elt', $money_end];
                }
            }
        }

        //上架中的商品  0=删除，2=上架中，1=仓库中
        $w['status'] = 2;
        //开始展示时间
        //$w['firsttime'] = ['elt', time()];
        //结束展示时间
        //$w['lasttime'] = ['egt', time()];
        if (isset($category)) {
            $c['topid'] = $category;
            $c['status'] = 'normal';
            $arr = [];
            $cateMenu = $this->category->where($c)->field('id')->select();

            //将顶级分类加入查询条件中
            $arr[] = $category;
            foreach ($cateMenu as $k => $v) {
                $arr[] = $v['id'];
            }

            $category = implode(",", $arr);
            $w['category_id'] = ['in', $category];
        }

        $page = request()->param('page');
        $page = !$page ? 1 : $page;

        if (request()->param('field') && request()->param('sort')) {
            $order = request()->param('field') . " " . request()->param('sort');
        } else {
            $order = "updatetime desc";
        }

        //$order =
        $list = $this->model->where($w)->order($order)->paginate(16, false, ['path' => 'javascript:ajaxPage([PAGE]);', 'page' => $page, 'var_page' => 'page']);

        $html = count($list, true) < 1 ? "<div class='col-xs-12'><small>没有符合条件的产品哦~</small></div>" : '';
        foreach ($list as $key => $vo) {
            $borderRight = ' goods-bottom-hr';
            $detailURL = addon_url('leescore/goods/details', array('gid' => $vo['id']));
            $disabled = ($vo['stock'] < 1) ? 'disabled' : '';
            $title_html = "<a class='product-title' href='" . $detailURL . "'>" . $vo['name'] . "</a></div>";
            $caseMoney = $title_html . "<div class=\"col-sm-12\"><div class='w50 padd-r-15 pull-left ovh fz-12'><span class=\"text-muted fz-12\">" . __('score') . "：" . $vo['scoreprice'] . "</span></div> <div class='w50 pull-left ovh text-muted fz-12'><span class=\"text-muted fz-12\">价格: " . $vo['money'] . __('money') . "</span></div></div><div class='clearfix'>";
            $beihuo = ((int)$vo['stock'] < 1) ? "备货中" : $vo['stock'];

            //var_dump($borderRight . $detailURL . $disabled .$caseMoney);

            $html .= "<li class=\"padding-bottom col-sm-6 col-md-4 col-lg-3 padding-top" . $borderRight . "\"><div class=\"f-list-box-item row\"><div class='products-img-box col-sm-12 col-xs-4'><a target='_blank' href=\"" . $detailURL . "\"><img class=\"pro-thumb center-block hidden-xs\" src=\"" . $vo['thumb'] . "\"><img class=\"pro-thumb visible-xs\" src=\"" . $vo['thumb'] . "\"></a></div><div class='col-xs-8 col-sm-12'><div class=\"col-sm-12\">" . $caseMoney . "</div><div class=\"col-sm-12\"><div class=\"w50 padd-r-15 text-muted pull-left ovh fz-12\">" . __('stock') . "：" . $beihuo . " </div></div><div class='clearfix'></div><div class=\"col-sm-12 margin-top text-center padding-none\"><a data-paramid=\"" . $vo['id'] . "\" data-url=\"" . addon_url('leescore/Cart/postCartAdd') . "\" href=\"javascript:;\" class=\"btn btn-success btn-sm add-cart " . $disabled . "\">+" . __('add cart') . "</a> &nbsp;&nbsp;&nbsp;<a target='_blank' href=\"" . $detailURL . "\" class=\"btn btn-danger btn-sm " . $disabled . "\">" . __('buy') . "</a></div><div class=\"clearfix\"></div></div></div></li></div>";
        }

        $html .= "<div class=\"clearfix\"></div>";
        $page = $list->render();
        return json(['list' => $html, 'page' => $page]);
    }

    //商品详情
    public function details()
    {
        $id = input('get.gid');
        $detail = $this->model->getGoodsDetail($id);
        $orderGoods = new OrderGoods();
        $article = $this->getSuipian(8);

        // 查看
        $users = $orderGoods->getPayUsers($detail['id']);
        $this->view->assign('item', $detail);
        $this->view->assign('users', $users);
        $this->view->assign('article', $article);
        return $this->view->fetch();
    }


    //已有订单，并数量相同直接读取现有的订单
    public function getOrderOne()
    {
        $id = $this->request->param('id');
        $map['id'] = $id;
        $order = Db::name('leescore_order')->where($map)->find();
        //dump($order);
        $info = $this->model->where("id", $order['goods_id'])->find();
        $w['userid'] = $this->auth->id;
        $w['isdel'] = 0;
        $addressList = Db::name('leescore_address')->where($w)->order('status desc')->select();
        $this->view->assign('add', $addressList);
        $this->view->assign('item', $info);
        $this->view->assign('order', $order);
        $this->view->engine->layout(false);
        return $this->view->fetch('getorderone');
    }
}

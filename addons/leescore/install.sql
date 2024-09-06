
SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for __PREFIX__leescore_address
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__leescore_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `zip` varchar(60) DEFAULT NULL COMMENT '邮编',
  `mobile` varchar(20) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `region` varchar(200) DEFAULT NULL COMMENT '省 / 区',
  `city` varchar(200) DEFAULT NULL COMMENT '城市',
  `xian` varchar(200) DEFAULT NULL COMMENT '县 / 区',
  `address` text,
  `status` int(11) DEFAULT NULL COMMENT '默认收货地址',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `truename` varchar(255) DEFAULT NULL COMMENT '真实姓名',
  `isdel` int(11) DEFAULT '0' COMMENT '逻辑删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='地址表';

-- ----------------------------
-- Table structure for __PREFIX__leescore_ads
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__leescore_ads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '广告标题',
  `thumb` char(255) DEFAULT NULL COMMENT '广告图片',
  `m_thumb` varchar(255) DEFAULT '' COMMENT '移动端',
  `open_mode` enum('0','1') DEFAULT '0' COMMENT '打开方式:0=原网页,1=新开页面',
  `path_url` varchar(255) DEFAULT '#' COMMENT '跳转地址',
  `position` enum('slider','activicy','other') DEFAULT 'other' COMMENT '广告位:slider=轮播处,activicy=热门活动,other=其它位置',
  `starttime` bigint(16) DEFAULT NULL COMMENT '起始时间',
  `endtime` bigint(16) DEFAULT NULL COMMENT '截止时间',
  `weigh` int(11) DEFAULT '50' COMMENT '排序',
  `status` enum('0','1') DEFAULT '0' COMMENT '状态:0=未启用,1=启用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='广告位管理';


-- ----------------------------
-- Table structure for __PREFIX__leescore_cart
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__leescore_cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `number` int(11) DEFAULT NULL,
  `goods_id` int(11) DEFAULT NULL COMMENT '商品ID',
  `createtime` bigint(16) DEFAULT NULL COMMENT '加入时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='购物车';

-- ----------------------------
-- Records of __PREFIX__leescore_cart
-- ----------------------------

-- ----------------------------
-- Table structure for __PREFIX__leescore_category
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__leescore_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` varchar(255) DEFAULT NULL COMMENT '菜单名',
  `topid` int(11) DEFAULT '0' COMMENT '顶级父类ID',
  `category_id` int(11) DEFAULT '0' COMMENT '上级菜单',
  `path` varchar(255) DEFAULT NULL COMMENT '完整路径',
  `weigh` int(11) DEFAULT '50' COMMENT '权重排序',
  `status` varchar(30) DEFAULT 'normal' COMMENT '状态',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `image` varchar(255) DEFAULT '' COMMENT '分类图片',
  `is_mobile` tinyint(1) DEFAULT '0' COMMENT '手机端展示',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COMMENT='商品分类';

-- ----------------------------
-- Table structure for __PREFIX__leescore_exchangelog
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__leescore_exchangelog` (
  `uid` int(11) DEFAULT NULL COMMENT '兑换用户',
  `goods_id` int(11) DEFAULT NULL COMMENT '商品ID',
  `order_id` int(11) DEFAULT NULL COMMENT '订单ID',
  `createtime` bigint(16) DEFAULT NULL COMMENT '兑换时间',
  `ip` varchar(60) DEFAULT NULL COMMENT '客户端Ip'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品购买日志';

-- ----------------------------
-- Table structure for __PREFIX__leescore_goods
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__leescore_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '商品ID',
  `category_id` int(11) DEFAULT NULL COMMENT '分类ID',
  `name` varchar(255) DEFAULT NULL COMMENT '商品标题',
  `paytype` enum('0','1','2') DEFAULT '0' COMMENT '出售模式:0=积分模式,1=货币模式,2=金钱+货币模式',
  `type` enum('0','2','1') DEFAULT '0' COMMENT '商品类型: 0=实物商品,1=虚拟商品,2=积分',
  `status` enum('0','1','2') DEFAULT '2' COMMENT '商品状态:0=删除,1=仓库,2=上架',
  `createtime` bigint(16) DEFAULT NULL COMMENT '商品发布时间',
  `body` text COMMENT '商品详情',
  `rule` text COMMENT '兑换规则',
  `thumb` varchar(255) DEFAULT NULL COMMENT '图片缩略图',
  `pics` text COMMENT '商品图集',
  `weigh` int(11) DEFAULT '50' COMMENT '权重排序',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `stock` int(11) DEFAULT '0' COMMENT '商品库存',
  `scoreprice` int(11) DEFAULT '0' COMMENT '所需积分',
  `firsttime` bigint(16) DEFAULT NULL COMMENT '开放时间',
  `lasttime` bigint(16) DEFAULT NULL COMMENT '结束时间',
  `money` float(11,2) DEFAULT '0.00' COMMENT '价格',
  `usenum` int(11) DEFAULT '0' COMMENT '已兑换',
  `flag` set('index','hot','recommend','new') DEFAULT '' COMMENT '推荐:index=首页,hot=热门,recommend=推荐,new=最新',
  `max_buy_number` int(11) DEFAULT '-1' COMMENT '限购数量',
  `fenxiao_status` enum('0','1') DEFAULT '0' COMMENT '商品分销: 0=不开启,1=开启',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COMMENT='推广类型';

-- ----------------------------
-- Table structure for __PREFIX__leescore_link
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__leescore_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `typeid` int(11) DEFAULT NULL COMMENT '链接分类',
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '链接名',
  `thumb` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '图片',
  `type` enum('3','2','1') COLLATE utf8mb4_unicode_ci DEFAULT '1' COMMENT '类型:1=单页,2=外链,3=其它碎片',
  `uri` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '跳转地址',
  `target` enum('_dialog','_self','_blank') COLLATE utf8mb4_unicode_ci DEFAULT '_dialog' COMMENT '打开方式:_dialog=使用Layer,_self=原网页,_blank=新开窗口',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
  `content` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='链接&单页';
INSERT INTO `__PREFIX__leescore_link` (`id`, `typeid`, `label`, `thumb`, `type`, `uri`, `target`, `title`, `content`) VALUES ('8', '7', '商品详情页警告语', '', '3', '', '_dialog', '商品兑换说明:', '1. 所有兑换的实物产品涉及到颜色的均为随机颜色，如商品名中有说明颜色，则为商品名中颜色。<br>\r\n2. 虚拟商品一经兑换，不可退货、换货。<br>\r\n3. 实物商品非质量问题不可退换，如衣服类商品如因尺码问题想退换，可联系客服协商。<br>\r\n4. 商品物流以官方安排物流为准，不接物流指定，如确需指定物流，可联系客服标注。<br>');

-- ----------------------------
-- Table structure for __PREFIX__leescore_link_category
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__leescore_link_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `switch` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '1' COMMENT '状态:1=开启,0=关闭',
  `thumb` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '图标',
  `type` enum('3','2','1') COLLATE utf8mb4_unicode_ci DEFAULT '1' COMMENT '分类类型: 1=普通类型,2=友情链接,3=其它碎片',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='链接分类';
INSERT INTO `__PREFIX__leescore_link_category` (`id`, `name`, `switch`, `thumb`, `type`) VALUES ('7', '其它碎片', '1', '', '3');

-- ----------------------------
-- Table structure for __PREFIX__leescore_order
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__leescore_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `uid` int(11) unsigned DEFAULT NULL COMMENT '用户ID',
  `order_id` varchar(255) NOT NULL COMMENT '订单号',
  `trade_id` varchar(255) DEFAULT NULL COMMENT '回执单号',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `pay` enum('0','1','2','3') DEFAULT '0' COMMENT '付款状态:0=未付款,1=已付款,2=已退款',
  `status` enum('-3','-2','-1','0','1','2','3','4','5') DEFAULT '0' COMMENT '订单状态:-3=异常订单,-2=驳回, -1=关闭订单,0=未支付,1=已支付,2=已发货,3=已签收,4=退货中,5=已退款',
  `paytime` bigint(16) DEFAULT NULL COMMENT '付款时间',
  `paytype` enum('score','money') DEFAULT 'score' COMMENT '支付方式:score=积分支付,money=余额支付',
  `isdel` int(11) DEFAULT '0' COMMENT '1',
  `other` text COMMENT '备注',
  `virtual_sn` varchar(255) DEFAULT NULL COMMENT '虚拟券序列号/快递单号',
  `virtual_name` varchar(255) DEFAULT NULL COMMENT '虚拟券名称/物流公司',
  `virtual_go_time` int(11) DEFAULT NULL COMMENT '发货时间',
  `virtual_sign_time` int(11) DEFAULT NULL COMMENT '签收时间',
  `score_total` int(11) DEFAULT '0' COMMENT '支付积分',
  `money_total` float(11,2) DEFAULT NULL COMMENT '支付货币',
  `result_other` varchar(255) DEFAULT NULL COMMENT '回馈备注',
  `trade_score` int(11) DEFAULT NULL COMMENT '实际支付积分',
  `trade_money` float(255,2) DEFAULT NULL COMMENT '实际付款',
  `trade_time` varchar(60) DEFAULT NULL COMMENT '实际付款时间',
  `auth_clear_level` int(11) DEFAULT '0' COMMENT '自动清理:0=清理,1=不清理',
  `pay_msg` varchar(255) DEFAULT '' COMMENT '订单处理结果',
  `weigh` int(11) DEFAULT '50',
  PRIMARY KEY (`id`),
  unique index order_id(order_id)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COMMENT='订单表';


-- ----------------------------
-- Table structure for __PREFIX__leescore_order_address
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__leescore_order_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `order_id` int(11) DEFAULT NULL,
  `zip` varchar(60) DEFAULT NULL COMMENT '邮编',
  `mobile` varchar(20) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `region` varchar(200) DEFAULT NULL COMMENT '省 / 区',
  `city` varchar(200) DEFAULT NULL COMMENT '城市',
  `xian` varchar(200) DEFAULT NULL COMMENT '县 / 区',
  `address` text,
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `truename` varchar(255) DEFAULT NULL COMMENT '真实姓名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='地址表';

-- ----------------------------
-- Table structure for __PREFIX__leescore_order_goods
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__leescore_order_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT NULL COMMENT '用户ID',
  `order_id` int(11) DEFAULT NULL COMMENT '订单ID',
  `goods_id` int(11) DEFAULT NULL COMMENT '商品ID',
  `buy_num` int(11) DEFAULT NULL COMMENT '购买数量',
  `score` decimal(10,0) DEFAULT NULL COMMENT '积分价格',
  `money` varchar(255) DEFAULT NULL COMMENT '货币价格',
  `goods_name` varchar(255) DEFAULT NULL COMMENT '商品名称',
  `goods_thumb` varchar(255) DEFAULT NULL COMMENT '商品缩略图',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COMMENT='订单-商品表';

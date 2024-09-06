
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

CREATE TABLE IF NOT EXISTS `__PREFIX__leescore_link_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `switch` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '1' COMMENT '状态:1=开启,0=关闭',
  `thumb` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '图标',
  `type` enum('3','2','1') COLLATE utf8mb4_unicode_ci DEFAULT '1' COMMENT '分类类型: 1=普通类型,2=友情链接,3=其它碎片',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='链接分类';

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

INSERT INTO `__PREFIX__leescore_link_category` (`id`, `name`, `switch`, `thumb`, `type`) VALUES ('7', '其它碎片', '1', '', '3');
INSERT INTO `__PREFIX__leescore_link` (`id`, `typeid`, `label`, `thumb`, `type`, `uri`, `target`, `title`, `content`) VALUES ('8', '7', '商品详情页警告语', '', '3', '', '_dialog', '商品兑换说明:', '1. 所有兑换的实物产品涉及到颜色的均为随机颜色，如商品名中有说明颜色，则为商品名中颜色。<br>\r\n2. 虚拟商品一经兑换，不可退货、换货。<br>\r\n3. 实物商品非质量问题不可退换，如衣服类商品如因尺码问题想退换，可联系客服协商。<br>\r\n4. 商品物流以官方安排物流为准，不接物流指定，如确需指定物流，可联系客服标注。<br>');
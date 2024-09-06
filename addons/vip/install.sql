CREATE TABLE IF NOT EXISTS `__PREFIX__vip` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `level` tinyint(1) unsigned DEFAULT '0' COMMENT 'vip等级',
  `group_id` int(10) unsigned DEFAULT '0' COMMENT '关联会员组',
  `name` varchar(100) DEFAULT '' COMMENT '名称',
  `label` varchar(100) DEFAULT '' COMMENT '标签',
  `intro` varchar(255) DEFAULT '' COMMENT '介绍',
  `image` varchar(255) DEFAULT '' COMMENT '图片',
  `icon` varchar(100) DEFAULT '' COMMENT '图标',
  `content` text COMMENT '内容',
  `price` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '价格',
  `pricedata` text COMMENT '价格配置',
  `rightdata` text COMMENT '权益配置',
  `sales` int(10) unsigned DEFAULT '0' COMMENT '销量',
  `createtime` bigint(16) DEFAULT NULL COMMENT '添加时间',
  `status` enum('normal','hidden','pulloff') DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `level` (`level`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COMMENT='VIP表';

CREATE TABLE IF NOT EXISTS `__PREFIX__vip_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `orderid` varchar(50) CHARACTER SET utf8 DEFAULT '' COMMENT '订单ID',
  `user_id` int(10) unsigned DEFAULT '0' COMMENT '会员ID',
  `vip_id` int(10) unsigned DEFAULT '0' COMMENT 'VIP ID',
  `record_id` int(10) unsigned DEFAULT '0' COMMENT '记录ID',
  `title` varchar(100) CHARACTER SET utf8 DEFAULT NULL COMMENT '订单标题',
  `amount` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '订单金额',
  `payamount` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '支付金额',
  `paytype` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT '支付类型',
  `paytime` bigint(16) DEFAULT NULL COMMENT '支付时间',
  `method` varchar(100) DEFAULT '' COMMENT '支付方式',
  `ip` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT 'IP地址',
  `useragent` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT 'UserAgent',
  `openid` varchar(100) DEFAULT '' COMMENT 'Openid',
  `memo` varchar(255) CHARACTER SET utf8 DEFAULT '' COMMENT '备注',
  `createtime` bigint(16) DEFAULT NULL COMMENT '添加时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `status` enum('created','paid','expired') CHARACTER SET utf8 DEFAULT 'created' COMMENT '状态:created=未支付,paid=已支付,expired=已过期',
  PRIMARY KEY (`id`),
  KEY `orderid` (`orderid`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COMMENT='VIP订单表';

CREATE TABLE IF NOT EXISTS `__PREFIX__vip_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT '0' COMMENT '会员ID',
  `vip_id` int(10) unsigned DEFAULT '0' COMMENT 'VIP ID',
  `level` int(10) unsigned DEFAULT '0' COMMENT 'VIP等级',
  `days` int(10) unsigned DEFAULT '0' COMMENT '天数',
  `amount` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '金额',
  `createtime` bigint(16) DEFAULT NULL COMMENT '添加时间',
  `expiretime` bigint(16) DEFAULT NULL COMMENT '过期时间',
  `status` enum('created','active','expired','finished','canceled','locked') DEFAULT 'created' COMMENT '状态:created=未支付,active=生效中,expired=已过期,finished=已完成,locked=已锁定,canceled=已取消',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COMMENT='VIP记录表';

ALTER TABLE `__PREFIX__user` ADD COLUMN `vip` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT 'VIP等级' AFTER `level`;
ALTER TABLE `__PREFIX__vip_order` ADD `openid` VARCHAR(100)  NULL  DEFAULT ''  COMMENT 'Openid'  AFTER `useragent`;
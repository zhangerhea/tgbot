
CREATE TABLE IF NOT EXISTS `__PREFIX__leesign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `sign_ip` varchar(15) DEFAULT NULL COMMENT '签到IP',
  `sign_time` datetime DEFAULT NULL COMMENT '签到时间',
  `sign_reward` int(11) DEFAULT NULL COMMENT '签到基础奖励',
  `sign_extra_reward` int(11) DEFAULT NULL COMMENT '连签额外奖励',
  `max_sign` int(11) DEFAULT NULL COMMENT '连签数',
  PRIMARY KEY (`id`)
) ENGINE=Innodb AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='用户签到表';

CREATE TABLE  IF NOT EXISTS `__PREFIX__faqueue_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `__PREFIX__faqueue_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) DEFAULT NULL COMMENT '队列名',
  `job` varchar(100) DEFAULT NULL COMMENT '执行类',
  `data` longtext COMMENT '任务数据',
  `create_time` int(10) DEFAULT NULL COMMENT '开始执行时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
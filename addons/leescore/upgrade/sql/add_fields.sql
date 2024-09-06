
alter table `__PREFIX__leescore_address` add `truename` varchar(255);
alter table `__PREFIX__leescore_ads` add `m_thumb` varchar(255);
alter table `__PREFIX__leescore_category` add `image` varchar(255);
alter table `__PREFIX__leescore_category` add `is_mobile` tinyint(1);
alter table `__PREFIX__leescore_order_goods` add `userid` int(11);
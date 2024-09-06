<?php

namespace addons\leescore\controller;

class ReturnCode
{
    const SUCCESS = 200;            // 操作成功
    const ERROR = -1;               // 错误
    const DANGER = -100;            // 非法操作
    const FAILED = 0;               // 操作失败
    const NO_SIGN = 1001;           // 还没有登录
    const PAY_SUCCESS = 1002;       // 购买成功
    const PAY_ERROR = 1003;         // 购买失败
    const DB_SAVE_ERROR = 1004;     // 保存失败
    const NO_STOCK = 1005;          // 商品没有库存
    const MAX_NUMBER = 1006;        // 购买数量已达上限
    const FOUND_DATA = 1007;        // 没有数据
    const DB_DELETE_FOUND = 1008;   // 执行删除操作错误
    const DOES_NOT_EXIST = 10404;   // 第三方依赖插件不存在
}

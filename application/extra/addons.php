<?php

return [
    'autoload' => false,
    'hooks' => [
        'epay_config_init' => [
            'epay',
        ],
        'addon_action_begin' => [
            'epay',
        ],
        'action_begin' => [
            'epay',
        ],
        'upgrade' => [
            'leescore',
        ],
        'app_init' => [
            'leescore',
        ],
        'leescorehook' => [
            'leescore',
        ],
        'user_sidenav_after' => [
            'leescore',
            'recharge',
            'vip',
        ],
        'leesignhook' => [
            'leesign',
        ],
        'config_init' => [
            'markdown',
        ],
        'upload_config_init' => [
            'vip',
        ],
        'user_login_successed' => [
            'vip',
        ],
    ],
    'route' => [
        '/leescore/goods$' => 'leescore/goods/index',
        '/leescore/order$' => 'leescore/order/index',
        '/score$' => 'leescore/index/index',
        '/address$' => 'leescore/address/index',
        '/leesign$' => 'leesign/index/index',
    ],
    'priority' => [],
    'domain' => '',
];

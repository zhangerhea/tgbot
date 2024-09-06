<?php

namespace addons\epay\library;

use addons\third\model\Third;
use app\common\library\Auth;
use Exception;
use think\Hook;
use think\Session;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Str;

/**
 * 订单服务类
 *
 * @package addons\epay\library
 */
class Service
{

    public const SDK_VERSION_V2 = 'v2';

    public const SDK_VERSION_V3 = 'v3';

    /**
     * 提交订单
     * @param array|float $amount    订单金额
     * @param string      $orderid   订单号
     * @param string      $type      支付类型,可选alipay或wechat
     * @param string      $title     订单标题
     * @param string      $notifyurl 通知回调URL
     * @param string      $returnurl 跳转返回URL
     * @param string      $method    支付方法
     * @param string      $openid    Openid
     * @param array       $custom    自定义微信支付宝相关配置
     * @return Response|RedirectResponse|Collection
     * @throws Exception
     */
    public static function submitOrder($amount, $orderid = null, $type = null, $title = null, $notifyurl = null, $returnurl = null, $method = null, $openid = '', $custom = [])
    {
        $version = self::getSdkVersion();
        $request = request();
        $addonConfig = get_addon_config('epay');

        if (!is_array($amount)) {
            $params = [
                'amount'    => $amount,
                'orderid'   => $orderid,
                'type'      => $type,
                'title'     => $title,
                'notifyurl' => $notifyurl,
                'returnurl' => $returnurl,
                'method'    => $method,
                'openid'    => $openid,
                'custom'    => $custom,
            ];
        } else {
            $params = $amount;
        }
        $type = isset($params['type']) && in_array($params['type'], ['alipay', 'wechat']) ? $params['type'] : 'wechat';
        $method = $params['method'] ?? 'web';
        $orderid = $params['orderid'] ?? date("YmdHis") . mt_rand(100000, 999999);
        $amount = $params['amount'] ?? 1;
        $title = $params['title'] ?? "支付";
        $auth_code = $params['auth_code'] ?? '';
        $openid = $params['openid'] ?? '';

        //自定义微信支付宝相关配置
        $custom = $params['custom'] ?? [];

        //未定义则使用默认回调和跳转
        $notifyurl = !empty($params['notifyurl']) ? $params['notifyurl'] : $request->root(true) . '/addons/epay/index/notifyx/paytype/' . $type;
        $returnurl = !empty($params['returnurl']) ? $params['returnurl'] : $request->root(true) . '/addons/epay/index/returnx/paytype/' . $type . '/out_trade_no/' . $orderid;

        $html = '';
        $config = Service::getConfig($type, array_merge($custom, ['notify_url' => $notifyurl, 'return_url' => $returnurl]));

        //判断是否移动端或微信内浏览器
        $isMobile = $request->isMobile();
        $isWechat = strpos($request->server('HTTP_USER_AGENT'), 'MicroMessenger') !== false;

        $result = null;
        if ($type == 'alipay') {
            //如果是PC支付,判断当前环境,进行跳转
            if ($method == 'web') {
                //如果是微信环境或后台配置PC使用扫码支付
                if ($isWechat || $addonConfig['alipay']['scanpay']) {
                    Session::set("alipayorderdata", $params);
                    $url = addon_url('epay/api/alipay', [], true, true);
                    return new RedirectResponse($url);
                } elseif ($isMobile) {
                    $method = 'wap';
                }
            }

            //创建支付对象
            $pay = Pay::alipay($config);
            $params = [
                'out_trade_no' => $orderid,//你的订单号
                'total_amount' => $amount,//单位元
                'subject'      => $title,
            ];

            switch ($method) {
                case 'web':
                    //电脑支付
                    $result = $pay->web($params);
                    break;
                case 'wap':
                    //手机网页支付
                    $result = $pay->wap($params);
                    break;
                case 'app':
                    //APP支付
                    $result = $pay->app($params);
                    break;
                case 'scan':
                    //扫码支付
                    $result = $pay->scan($params);
                    break;
                case 'pos':
                    //刷卡支付必须要有auth_code
                    $params['auth_code'] = $auth_code;
                    $result = $pay->pos($params);
                    break;
                case 'mini':
                case 'miniapp':
                    //小程序支付
                    //小程序支付,直接返回字符串
                    //小程序支付必须要有buyer_id，这里使用openid
                    $params['buyer_id'] = $openid;
                    $result = $pay->mini($params);
                    break;
                default:
            }
        } else {
            //如果是PC支付,判断当前环境,进行跳转
            if ($method == 'web') {
                //如果是移动端，但不是微信环境
                if ($isMobile && !$isWechat) {
                    $method = 'wap';
                } else {
                    Session::set("wechatorderdata", $params);
                    $url = addon_url('epay/api/wechat', [], true, true);
                    return new RedirectResponse($url);
                }
            }

            //单位分
            $total_fee = function_exists('bcmul') ? bcmul($amount, 100) : $amount * 100;
            $total_fee = (int)$total_fee;
            $ip = $request->ip();
            //微信服务商模式时需传递sub_openid参数
            $openidName = $addonConfig['wechat']['mode'] == 'service' ? 'sub_openid' : 'openid';

            //创建支付对象
            $pay = Pay::wechat($config);

            if (self::isVersionV3()) {
                //V3支付
                $params = [
                    'out_trade_no' => $orderid,
                    'description'  => $title,
                    'amount'       => [
                        'total' => $total_fee,
                    ]
                ];
                switch ($method) {
                    case 'mp':
                        //公众号支付
                        //公众号支付必须有openid
                        $params['payer'] = [$openidName => $openid];
                        $result = $pay->mp($params);
                        break;
                    case 'wap':
                        //手机网页支付,跳转
                        $params['scene_info'] = [
                            'payer_client_ip' => $ip,
                            'h5_info'         => [
                                'type' => 'Wap',
                            ]
                        ];
                        $result = $pay->wap($params);
                        break;
                    case 'app':
                        //APP支付,直接返回字符串
                        $result = $pay->app($params);
                        break;
                    case 'scan':
                        //扫码支付,直接返回字符串
                        $result = $pay->scan($params);
                        break;
                    case 'pos':
                        //刷卡支付,直接返回字符串
                        //刷卡支付必须要有auth_code
                        $params['auth_code'] = $auth_code;
                        $result = $pay->pos($params);
                        break;
                    case 'mini':
                    case 'miniapp':
                        //小程序支付,直接返回字符串
                        //小程序支付必须要有openid
                        $params['payer'] = [$openidName => $openid];
                        $result = $pay->mini($params);
                        break;
                    default:
                }
            } else {
                //V2支付
                $params = [
                    'out_trade_no' => $orderid,
                    'body'         => $title,
                    'total_fee'    => $total_fee,
                ];
                switch ($method) {
                    case 'mp':
                        //公众号支付
                        //公众号支付必须有openid
                        $params[$openidName] = $openid;
                        $result = $pay->mp($params);
                        break;
                    case 'wap':
                        //手机网页支付,跳转
                        $params['spbill_create_ip'] = $ip;
                        $result = $pay->wap($params);
                        break;
                    case 'app':
                        //APP支付,直接返回字符串
                        $result = $pay->app($params);
                        break;
                    case 'scan':
                        //扫码支付,直接返回字符串
                        $result = $pay->scan($params);
                        break;
                    case 'pos':
                        //刷卡支付,直接返回字符串
                        //刷卡支付必须要有auth_code
                        $params['auth_code'] = $auth_code;
                        $result = $pay->pos($params);
                        break;
                    case 'mini':
                    case 'miniapp':
                        //小程序支付,直接返回字符串
                        //小程序支付必须要有openid
                        $params[$openidName] = $openid;
                        $result = $pay->miniapp($params);
                        break;
                    default:
                }
            }
        }

        //使用重写的Response类、RedirectResponse、Collection类
        if ($result instanceof \Symfony\Component\HttpFoundation\RedirectResponse) {
            $result = new RedirectResponse($result->getTargetUrl());
        } elseif ($result instanceof \Symfony\Component\HttpFoundation\Response) {
            $result = new Response($result->getContent());
        } elseif ($result instanceof \Yansongda\Supports\Collection) {
            $result = Collection::make($result->all());
        } elseif ($result instanceof \GuzzleHttp\Psr7\Response) {
            $result = new Response($result->getBody());
        }

        return $result;
    }

    /**
     * 验证回调是否成功
     * @param string $type   支付类型
     * @param array  $custom 自定义配置信息
     * @return bool|\Yansongda\Pay\Gateways\Alipay|\Yansongda\Pay\Gateways\Wechat|\Yansongda\Pay\Provider\Wechat|\Yansongda\Pay\Provider\Alipay
     */
    public static function checkNotify($type, $custom = [])
    {
        $type = strtolower($type);
        if (!in_array($type, ['wechat', 'alipay'])) {
            return false;
        }

        $version = self::getSdkVersion();

        try {
            $config = self::getConfig($type, $custom);
            $pay = $type == 'wechat' ? Pay::wechat($config) : Pay::alipay($config);

            $data = Service::isVersionV3() ? $pay->callback() : $pay->verify();
            if ($type == 'alipay') {
                if (in_array($data['trade_status'], ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
                    return $pay;
                }
            } else {
                return $pay;
            }
        } catch (Exception $e) {
            \think\Log::record("回调请求参数解析错误", "error");
            return false;
        }

        return false;
    }

    /**
     * 验证返回是否成功，请勿用于判断是否支付成功的逻辑验证
     * 已弃用
     *
     * @param string $type   支付类型
     * @param array  $custom 自定义配置信息
     * @return bool
     * @deprecated  已弃用，请勿用于逻辑验证
     */
    public static function checkReturn($type, $custom = [])
    {
        //由于PC及移动端无法获取请求的参数信息，取消return验证，均返回true
        return true;
    }

    /**
     * 获取配置
     * @param string $type   支付类型
     * @param array  $custom 自定义配置，用于覆盖插件默认配置
     * @return array
     */
    public static function getConfig($type = 'wechat', $custom = [])
    {
        $addonConfig = get_addon_config('epay');
        $config = $addonConfig[$type] ?? $addonConfig['wechat'];

        // SDK版本
        $version = self::getSdkVersion();

        if (isset($config['cert_client']) && substr($config['cert_client'], 0, 8) == '/addons/') {
            $config['cert_client'] = ROOT_PATH . str_replace('/', DS, substr($config['cert_client'], 1));
        }
        if (isset($config['cert_key']) && substr($config['cert_key'], 0, 8) == '/addons/') {
            $config['cert_key'] = ROOT_PATH . str_replace('/', DS, substr($config['cert_key'], 1));
        }
        if (isset($config['app_cert_public_key']) && substr($config['app_cert_public_key'], 0, 8) == '/addons/') {
            $config['app_cert_public_key'] = ROOT_PATH . str_replace('/', DS, substr($config['app_cert_public_key'], 1));
        }
        if (isset($config['alipay_root_cert']) && substr($config['alipay_root_cert'], 0, 8) == '/addons/') {
            $config['alipay_root_cert'] = ROOT_PATH . str_replace('/', DS, substr($config['alipay_root_cert'], 1));
        }
        if (isset($config['ali_public_key']) && (Str::endsWith($config['ali_public_key'], '.crt') || Str::endsWith($config['ali_public_key'], '.pem'))) {
            $config['ali_public_key'] = ROOT_PATH . str_replace('/', DS, substr($config['ali_public_key'], 1));
        }

        // V3支付
        if (self::isVersionV3()) {
            if ($type == 'wechat') {
                $config['mp_app_id'] = $config['app_id'] ?? '';
                $config['app_id'] = $config['appid'] ?? '';
                $config['mini_app_id'] = $config['miniapp_id'] ?? '';
                $config['combine_mch_id'] = $config['combine_mch_id'] ?? '';
                $config['mch_secret_key'] = $config['key_v3'] ?? '';
                $config['mch_secret_cert'] = $config['cert_key'];
                $config['mch_public_cert_path'] = $config['cert_client'];

                $config['sub_mp_app_id'] = $config['sub_appid'] ?? '';
                $config['sub_app_id'] = $config['sub_app_id'] ?? '';
                $config['sub_mini_app_id'] = $config['sub_miniapp_id'] ?? '';
                $config['sub_mch_id'] = $config['sub_mch_id'] ?? '';
            } elseif ($type == 'alipay') {
                $config['app_secret_cert'] = $config['private_key'] ?? '';
                $config['app_public_cert_path'] = $config['app_cert_public_key'] ?? '';
                $config['alipay_public_cert_path'] = $config['ali_public_key'] ?? '';
                $config['alipay_root_cert_path'] = $config['alipay_root_cert'] ?? '';
                $config['service_provider_id'] = $config['pid'] ?? '';
            }
            $modeArr = ['normal' => 0, 'dev' => 1, 'service' => 2];
            $config['mode'] = $modeArr[$config['mode']] ?? 0;
        }

        // 日志
        if ($config['log']) {
            $config['log'] = [
                'enable' => true,
                'file'   => LOG_PATH . 'epaylogs' . DS . $type . '-' . date("Y-m-d") . '.log',
                'level'  => 'debug'
            ];
        } else {
            $config['log'] = [
                'enable' => false,
            ];
        }

        // GuzzleHttp配置，可选
        $config['http'] = [
            'timeout'         => 10,
            'connect_timeout' => 10,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ];

        $config['notify_url'] = empty($config['notify_url']) ? addon_url('epay/api/notifyx', [], false) . '/type/' . $type : $config['notify_url'];
        $config['notify_url'] = !preg_match("/^(http:\/\/|https:\/\/)/i", $config['notify_url']) ? request()->root(true) . $config['notify_url'] : $config['notify_url'];
        $config['return_url'] = empty($config['return_url']) ? addon_url('epay/api/returnx', [], false) . '/type/' . $type : $config['return_url'];
        $config['return_url'] = !preg_match("/^(http:\/\/|https:\/\/)/i", $config['return_url']) ? request()->root(true) . $config['return_url'] : $config['return_url'];

        //合并自定义配置
        $config = array_merge($config, $custom);

        //v3版本时返回的结构不同
        if (self::isVersionV3()) {
            $config = [$type => ['default' => $config], 'logger' => $config['log'], 'http' => $config['http'], '_force' => true];

        }
        return $config;
    }

    /**
     * 获取微信Openid
     *
     * @param array $custom 自定义配置信息
     * @return mixed|string
     */
    public static function getOpenid($custom = [])
    {
        $openid = '';
        $auth = Auth::instance();
        if ($auth->isLogin()) {
            $third = get_addon_info('third');
            if ($third && $third['state']) {
                $thirdInfo = Third::where('user_id', $auth->id)->where('platform', 'wechat')->where('apptype', 'mp')->find();
                $openid = $thirdInfo ? $thirdInfo['openid'] : '';
            }
        }
        if (!$openid) {
            $openid = Session::get("openid");

            //如果未传openid，则去读取openid
            if (!$openid) {
                $addonConfig = get_addon_config('epay');
                $wechat = new Wechat($custom['app_id'] ?? $addonConfig['wechat']['app_id'], $custom['app_secret'] ?? $addonConfig['wechat']['app_secret']);
                $openid = $wechat->getOpenid();
            }
        }
        return $openid;
    }

    /**
     * 获取SDK版本
     * @return mixed|string
     */
    public static function getSdkVersion()
    {
        $addonConfig = get_addon_config('epay');
        return $addonConfig['version'] ?? self::SDK_VERSION_V2;
    }

    /**
     * 判断是否V2支付
     * @return bool
     */
    public static function isVersionV2()
    {
        return self::getSdkVersion() === self::SDK_VERSION_V2;
    }

    /**
     * 判断是否V3支付
     * @return bool
     */
    public static function isVersionV3()
    {
        return self::getSdkVersion() === self::SDK_VERSION_V3;
    }
}

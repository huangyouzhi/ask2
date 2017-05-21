<?php

!defined('IN_ASK2') && exit('Access Denied');
require ASK2_ROOT . '/lib/alipay/alipay_service.class.php';
require ASK2_ROOT . '/lib/alipay/alipay_notify.class.php';

class ebankmodel {

    var $db;
    var $base;

    function __construct(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function aliapytransfer($rechargemoney,$mtype="财富充值") {
        $aliapy_config = include ASK2_ROOT . '/data/alipay.config.php';
        $tradeid = "u-" . strtolower(random(6));
        //构造要请求的参数数组
        $parameter = array(
            "service" => "create_direct_pay_by_user",
            "payment_type" => "1",
            "partner" => trim($aliapy_config['partner']),
            "_input_charset" => trim(strtolower($aliapy_config['input_charset'])),
            "seller_email" => trim($aliapy_config['seller_email']),
            "return_url" => trim($aliapy_config['return_url']),
            "notify_url" => trim($aliapy_config['notify_url']),
            "out_trade_no" => $tradeid,
            "subject" => $mtype,
            "body" => $mtype,
            "total_fee" => $rechargemoney,
            "paymethod" => '',
            "defaultbank" => '',
            "anti_phishing_key" => '',
            "exter_invoke_ip" => '',
            "show_url" => '',
            "extra_common_param" => '',
            "royalty_type" => '',
            "royalty_parameters" => ''
        );
        //构造即时到帐接口
        $alipayService = new AlipayService($aliapy_config);
        $html_text = $alipayService->create_direct_pay_by_user($parameter);
        echo $html_text;
    }

    /**
     * 针对return_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
    function aliapyverifyreturn() {
        $aliapy_config = include ASK2_ROOT . '/data/alipay.config.php';
        $alipayNotify = new AlipayNotify($aliapy_config, $this->base->get, $this->base->post);
        return $alipayNotify->verifyReturn();
    }

}

?>
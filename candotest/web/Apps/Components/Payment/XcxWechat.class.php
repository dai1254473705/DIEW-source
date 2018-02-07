<?php

/**
 * @file XcxWechat.class.php
 * @brief 微信小程序支付]
 * @author pulingao
 * @date 2017年6月22日
 * @version 1.0.0
 * @note 配置类
 */

namespace Components\Payment;

class XcxWechat {
    
    private $appId = 'wx7590cffee6387a75';
    private $appSecret = '9a0003e7f6b72a1ef2b60b73d35beeab';
    private $mch_id = '1249485301';
    private $apiKey = 'Blo59fw5yWmYuBqSGhhlOiIoy4G5WxhU';


    //支付插件名称
    public $name = '微信小程序支付';
    
    /**
     * @see getSubmitUrl()
     */
    public function getSubmitUrl() {
        return 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    }

    /**
     * @see notifyStop()
     */
    public function notifyStop($status = true, $msg = '') {
        if (!$status) {
            die("<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[" . $msg . "]]></return_msg></xml>");
        }
        die("<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>");
    }

    /**
     * @see callback()
     */
    public function callback($callbackData) {
        
    }

    /**
     * @see serverCallback()
     */
    public function serverCallback($callbackData) {
        $postXML = file_get_contents("php://input");
        $callbackData = $this->converArray($postXML);
        
        file_put_contents('/tmp/xxx/log.txt', "\r\n".var_export($callbackData, true), FILE_APPEND);
        
        if (isset($callbackData['return_code']) && $callbackData['return_code'] == 'SUCCESS') {
            //除去待签名参数数组中的空值和签名参数
            $para_filter = $this->paraFilter($callbackData);

            //对待签名参数数组排序
            $para_sort = $this->argSort($para_filter);

            //生成签名结果
            $mysign = $this->buildMysign($para_sort, $this->apiConfig['key']);

            //验证签名，成功之后返回需要的参数
            if ($mysign == $callbackData['sign']) {
                if ($callbackData['result_code'] == 'SUCCESS') {
                    $orderNo = $callbackData['out_trade_no'];
                    $money = $callbackData['total_fee'] / 100; 
                    $callbackData['money_paid'] = $money;
                    return $callbackData;
                    
                } else {
                    $message = $callbackData['err_code_des'];
                }
            } else {
                $message = '签名不匹配';
            }
        }

        $message = $message ? $message : $callbackData['message'];
        $this->notifyStop(false, $message);
        return false;
    }

    /**
     * 统一下单
     * @see uniformOrders()
     */
    public function uniformOrders($payment) {
        $return = array();

        //基本参数
        $return['appid'] = $this->appId;
        $return['mch_id'] = $this->mch_id;
        $return['nonce_str'] = (string) createRandString(true, 16, 4, '');
        $return['body'] = $payment['body'] ? ($payment['body'].'YouCanDo-知识付费时代') : 'YouCanDo-知识付费时代';
        $return['out_trade_no'] = $payment['out_trade_no'];
        $return['total_fee'] = $payment['price'] * 100;
        $return['spbill_create_ip'] = get_client_ip();
        
        # {attention} 微信使用xml传输回调数据，因此此处单独设置微信的回调地址，前期没有考虑明白，后期再优化
        $return['notify_url'] = $payment['notify_url'];

        $return['trade_type'] = 'JSAPI';
        $return['openid'] = $payment['openid'];
        
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($return);
        
        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);
        
        //生成签名结果
        $mysign = $this->buildMysign($para_sort, $this->apiKey);
        
        //签名结果与签名方式加入请求提交参数组中
        $return['sign'] = $mysign;

        $xmlData = $this->converXML($return);
        $result = $this->curlSubmit($xmlData);
        
        //进行与支付订单处理
        $resultArray = $this->converArray($result);
        
        return $resultArray;
    }
    
    
    /**
     * app端根据prepay_id调起微信支付需要的参数
     * @param type $prepay_id
     * @return string
     */
    public function appPayTuneUpParams($prepay_id) {
        if (!$prepay_id) {
            return '';
        }
        $params = array(
            'appId' => $this->appId,
            'timeStamp' => (string) time(),
            'nonceStr' => (string) createRandString(true, 16, 4, ''),
            'package' => 'prepay_id='.$prepay_id,
            'signType' => 'MD5',
        );
        $sign = $this->generateSign($params);
        $params['sign'] = $sign;
        unset($params['appId']);
        
        return $params;
    }
    
    /**
     * 生成签名数据
     * @param type $signParams
     * @return string
     */
    public function generateSign($signParams) {
        
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($signParams);
        
        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);
        
        //生成签名结果
        $mysign = $this->buildMysign($para_sort, $this->apiKey);
        
        //签名结果与签名方式加入请求提交参数组中
        return $mysign;
    }

    /**
     * @brief 提交数据
     * @param xml $xmlData 要发送的xml数据
     * @return xml 返回数据
     */
    private function curlSubmit($xmlData) {
        //接收xml数据的文件
        $url = $this->getSubmitUrl();

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/xml', 'Content-Type: application/xml'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * @see doPay()
     * @param type $sendData    发送的数据
     * @param type $order_no    订单编号
     */
    public function doPay($sendData, $order_no = null) {
        if (isset($sendData['prepay_id']) && $sendData['prepay_id']) {
            $return = array();

            //基本参数
            $return['appId'] = $sendData['appid'];
            $return['timeStamp'] = time();
            $return['nonceStr'] = rand(100000, 999999);
            $return['package'] = "prepay_id=" . $sendData['prepay_id'];
            $return['signType'] = "MD5";

            //除去待签名参数数组中的空值和签名参数
            $para_filter = $this->paraFilter($return);

            //对待签名参数数组排序
            $para_sort = $this->argSort($para_filter);

            //生成签名结果
            $mysign = $this->buildMysign($para_sort, $sendData['key']);

            //签名结果与签名方式加入请求提交参数组中
            $return['paySign'] = $mysign;
            $return['successUrl'] = U('Home/OrderInfo/payed', array('order_sn' => $order_no), false, true);
            $return['failUrl'] = U('Home/OrderInfo/payError', array('order_sn' => $order_no, 'msg' => "支付失败"), false, true);
            
            // 调用 js_api 支付
            require_once dirname(__FILE__) . '/template/pay.php';
            
        } else {
            
            $url = U('Home/OrderInfo/payError', array('order_sn' => $order_no, 'msg' => "微信下单API接口失败"), false, true);
            redirect($url);
            exit;
        }
    }

    /**
     * @brief 从array到xml转换数据格式
     * @param array $arrayData
     * @return xml
     */
    private function converXML($arrayData) {
        $xml = '<xml>';
        foreach ($arrayData as $key => $val) {
            $xml .= '<' . $key . '><![CDATA[' . $val . ']]></' . $key . '>';
        }
        $xml .= '</xml>';
        return $xml;
    }

    /**
     * @brief 从xml到array转换数据格式
     * @param xml $xmlData
     * @return array
     */
    private function converArray($xmlData) {
        $result = array();
        $xmlHandle = xml_parser_create();
        xml_parse_into_struct($xmlHandle, $xmlData, $resultArray);

        foreach ($resultArray as $key => $val) {
            if ($val['tag'] != 'XML') {
                $result[$val['tag']] = $val['value'];
            }
        }
        return array_change_key_case($result);
    }

    /**
     * 除去数组中的空值和签名参数
     * @param $para 签名参数组
     * return 去掉空值与签名参数后的新签名参数组
     */
    private function paraFilter($para) {
        $para_filter = array();
        foreach ($para as $key => $val) {
            if ($key == "sign" || $key == "sign_type" || $val == "") {
                continue;
            } else {
                $para_filter[$key] = $para[$key];
            }
        }
        return $para_filter;
    }

    /**
     * 对数组排序
     * @param $para 排序前的数组
     * return 排序后的数组
     */
    private function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * 生成签名结果
     * @param $sort_para 要签名的数组
     * @param $key 交易安全校验码
     * @param $sign_type 签名类型 默认值：MD5
     * return 签名结果字符串
     */
    private function buildMysign($sort_para, $key, $sign_type = "MD5") {
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($sort_para);
        //把拼接后的字符串再与安全校验码直接连接起来
        $prestr = $prestr . '&key=' . $key;
        //把最终的字符串签名，获得签名结果
        $mysgin = md5($prestr);
        return strtoupper($mysgin);
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    private function createLinkstring($para) {
        $arg = "";
        foreach ($para as $key => $val) {
            $arg.=$key . "=" . $val . "&";
        }

        //去掉最后一个&字符
        $arg = trim($arg, '&');

        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    /**
     * @param 获取配置参数
     */
    public function configParam() {
        $result = array(
            'mch_id' => '商户号',
            'appid' => '商户公众号AppID',
            'appsecret' => '商户公众号AppSecret',
            'key' => '商户支付密钥',
        );
        return $result;
    }

}

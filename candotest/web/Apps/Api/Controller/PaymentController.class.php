<?php

/**
 * 支付功能处理
 */

namespace Api\Controller;

use Think\Controller;

class PaymentController extends CommonController {

    public function _initialize() {
        parent::_initialize(false);
    }

    /**
     * 调用统一下单，生成预付款ID，回传給客户端
     */
    public function create() {
        
        $this->checkToken();
        
        $signCode = $this->params['sign_code'];
        $inquire_id = $this->params['id'];
        $inquireModel = new \Common\Model\InquireModel();
        $inquire = $inquireModel->getByPk($inquire_id);
        if (!$inquire) {
            $this->fail_exit('数据异常，请刷新重试');
        }
        
        # 用户的openid
        $userModel = new \Common\Model\UserModel();
        $openid = $userModel->getOpenIdByUid(self::$uid);
        if (!$openid) {
            $this->fail_exit('数据异常，请刷新重试');
        }
        
        # 调用小程序支付的统一下单，生成预付款单号
        $xcxPayment = new \Components\Payment\XcxWechat();
        $orderSn = 'YCD' . time() . mt_rand(100, 999);
        $payment = array(
            'body' => $inquire['title'],
            'out_trade_no' => $orderSn,
            'price' => $inquire['price'],
            'notify_url' => $this->params['debug'] ? 'http://www.cando.com/wechat/server_callback' : 'https://www.hasoffer.com/cando/wechat/server_callback',
            'openid' => $openid,
        );
        
        $resultArray = $xcxPayment->uniformOrders($payment);
        
        //处理错误
        if (isset($resultArray['err_code']) && isset($resultArray['err_code_des'])) {
            $this->fail_exit('调起支付失败，请联系管理员');
        }
        
        //处理正确，得到prepay_id之后，生成客户端支付需要的参数
        if (isset($resultArray['return_code']) && $resultArray['return_code'] == 'SUCCESS' && $resultArray['prepay_id']) {
            $prepay_id = $resultArray['prepay_id'];
            
            # bulid
            $appPayParams = $xcxPayment->appPayTuneUpParams($prepay_id);
            
            # 创建订单数据，并返回给客户端支付回调的识别码
            $orderModel = new \Common\Model\OrderModel();
            $add = array(
                'inquire_id' => $inquire_id,
                'inquire_title' => $inquire['title'],
                'order_sn' => $orderSn,
                'total_fee' => $inquire['price'],
                'user_id' => self::$uid,
                'prepay_id' => $prepay_id,
                'pay_sign_code' => $signCode ? $signCode : '',
                'status' => \Common\Model\OrderModel::STATUS_UNPAID,
                'create_time' => time()
            );
            $orderId = $orderModel->add($add);
            if (!$orderId) {
                $this->fail_exit('调起支付失败，请重试');
            }
            
            $order_code = encryption(encryption($orderId.'||'.$orderSn));
            $appPayParams['order_code'] = $order_code;
            
            $this->success_exit($appPayParams);
        }
        
        $this->fail_exit('未知错误');
    }
    
    
    /**
     * 小程序支付的同步回调
     */
    public function callback() {
        
        $this->checkToken();
        
        $code = $this->params['code'];
        $sign = $this->params['sign'];
        
        # 验证签名
        $encryptKey = C('ENCRYPTION_KEY');
        $serverSign = md5($code.$encryptKey);
        if ($serverSign != $sign) {
            $this->fail_exit('签名验证失败，请在微信中直接支付');
        }
        
        $codeString = encryption(encryption($code, false), false);
        list($order_id, $order_sn) = explode('||', $codeString);
        $orderModel = new \Common\Model\OrderModel();
        $orderInfo = $orderModel->getByPk($order_id);
        if (!$orderInfo) {
            $this->fail_exit('数据异常，请联系管理员');
        }
        if ($orderInfo['status'] == \Common\Model\OrderModel::STATUS_PAID) {
            $this->fail_exit('此内容已支付，请不要重复支付');
        }
        
        ###### 第一步，开始给收益
        $incomeModel = new \Common\Model\IncomeModel();
        $payStatus = $incomeModel->payUserIncome($orderInfo, self::$uid);
        if (!$payStatus) {
            $this->fail_exit('数据异常，请联系管理员');
        }
        
        ###### 第二步，更改订单状态
        $orderUp = array(
            'id' => $order_id,
            'status' => \Common\Model\OrderModel::STATUS_PAID,
        );
        $orderModel->save($orderUp);
        
        ###### 第三步，递增付费查看人数
        $historyModel = new \Common\Model\HistoryModel();
        $payNumStatus = $historyModel->incrPayNum($orderInfo, self::$uid);
        
        $this->success_exit();
    }
    

    /**
     * 微信回调
     * 测试，微信小程序并没有在付款后异步通知，因此，直接同步处理
     */
    public function serverCallback() {
        $xcxPayment = new \Components\Payment\XcxWechat();
        $callbackData = $xcxPayment->serverCallback();
    }

}

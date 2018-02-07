<?php

/**
 * 用户信息处理
 */

namespace Api\Controller;

use Think\Controller;

class UserController extends CommonController {

    public function _initialize() {
        parent::_initialize(false);
    }

    /**
     * 校验用户数据
     */
    public function verify() {

        $userParams = $this->params;
        $userInfo = json_decode($userParams['user'], true);

        # 获取必要参数，换取session_key
        $code = $userParams['code'];
        if (!$code) {
            $this->fail_exit('授权失败，请退出后重新授权');
        }

        $codeSession = \Api\Service\WxLoginService::WxJsCodeToSession($code);
        if (!is_array($codeSession)) {
            $this->fail_exit('授权失败，请退出后重新授权');
        }

        # 授权成功，保存openid，换取token
        $openid = $codeSession['openid'];
        $userModel = new \Common\Model\UserModel();
        $user_id = $userModel->getUidByOpenId($openid);
        if ($user_id) {

            # 设置最后一次登录的时间
            $up = array(
                'id' => $user_id,
                'last_login' => time(),
                'last_ip' => get_client_ip()
            );
            $upSta = $userModel->save($up);

            $userData = $userModel->getUserByUid($user_id);
            $this->success_exit($userData);
        }

        # 没有此用户，开始创建
        $wxAdd = array(
            'openid' => $openid,
            'unionid' => '',
            'groupid' => '',
            'subscribe' => 0,
            'nickname' => $userInfo['userInfo']['nickName'],
            'token' => genToken(),
            'last_login' => time(),
            'last_ip' => get_client_ip(),
            'sex' => $userInfo['userInfo']['gender'],
            'language' => $userInfo['userInfo']['language'],
            'city' => $userInfo['userInfo']['city'],
            'province' => $userInfo['userInfo']['province'],
            'country' => $userInfo['userInfo']['country'],
            'headimgurl' => $userInfo['userInfo']['avatarUrl'],
            'remark' => $userInfo['encryptedData'],
            'extend' => $this->params['params'],
            'create_time' => time(),
        );
        $addUserId = $userModel->add($wxAdd);

        $userData = $userModel->getUserByUid($addUserId);
        $this->success_exit($userData);
    }

    /**
     * 获取用户的个人信息
     */
    public function my() {
        $this->checkToken();

        $incomeModel = new \Common\Model\IncomeModel();
        $yestodayStartTime = date('Y-m-d 00:00:00', strtotime('-1 day'));
        $unixStartTime = strtotime($yestodayStartTime);
        $yestodayCond = array(
            'income_user_id' => self::$uid,
            'create_time' => array('between', $unixStartTime . ',' . ($unixStartTime + 86400))
        );

        # 昨日
        $yestodayIncome = $incomeModel->field('SUM(`income`) as income_sum')->where($yestodayCond)->find();
        $yestodayIncomeTotal = $yestodayIncome['income_sum'] ? $yestodayIncome['income_sum'] : 0;

        # 累积
        $totalIncome = $incomeModel->field('SUM(`income`) as income_sum')->where(array('income_user_id' => self::$uid))->find();
        $allIncomeTotal = $totalIncome['income_sum'] ? $totalIncome['income_sum'] : 0;

        # 提现的金额
        $makeCashModel = new \Common\Model\MakeCashModel();
        $totalCash = $makeCashModel->field('SUM(`cash_total`) as cash_total_sum')->where(array('user_id' => self::$uid))->find();
        $allCashTotal = $totalCash['cash_total_sum'] ? $totalCash['cash_total_sum'] : 0;

        # 正在提现的金额
        $progressCash = $makeCashModel->field('SUM(`cash_total`) as cash_total_sum')->where(
                        array(
                            'user_id' => self::$uid,
                            'status' => \Common\Model\MakeCashModel::STATUS_PROGREING,
                        )
                )->find();
        $progressCashTotal = $progressCash['cash_total_sum'] ? $progressCash['cash_total_sum'] : 0;

        $result = array(
        'yes_income' => $yestodayIncomeTotal,
        'total_income' => $allIncomeTotal,
        'make_cash_progressing' => $progressCashTotal,
        'can_make_cash' => $allIncomeTotal - $allCashTotal,
        );

        $this->success_exit($result);
    }
    
    
    /**
     * 提现
     */
    public function makeCash() {
        $this->checkToken();
        
        $can_make_cash = $this->params['make_cash'];
        if ($can_make_cash <= 0) {
            $this->fail_exit('无可提现金额');
        }
        
        # 累积
        $incomeModel = new \Common\Model\IncomeModel();
        $totalIncome = $incomeModel->field('SUM(`income`) as income_sum')->where(array('income_user_id' => self::$uid))->find();
        $allIncomeTotal = $totalIncome['income_sum'] ? $totalIncome['income_sum'] : 0;

        # 提现的金额
        $makeCashModel = new \Common\Model\MakeCashModel();
        $totalCash = $makeCashModel->field('SUM(`cash_total`) as cash_total_sum')->where(array('user_id' => self::$uid))->find();
        $allCashTotal = $totalCash['cash_total_sum'] ? $totalCash['cash_total_sum'] : 0;
        if ($can_make_cash > ($allIncomeTotal - $allCashTotal)) {
            $this->fail_exit('可提现金额超出');
        }
        
        $add = array(
            'user_id' => self::$uid,
            'cash_total' => $can_make_cash,
            'status' => \Common\Model\MakeCashModel::STATUS_PROGREING,
            'create_time' => time()
        );
        
        $addId = $makeCashModel->add($add);
        
        $this->success_exit();
    }

}

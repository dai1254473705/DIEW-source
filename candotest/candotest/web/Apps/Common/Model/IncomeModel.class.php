<?php

/**
 * 收入 数据模型 
 */

namespace Common\Model;

use Think\Model;
use Common\Model\AgileModel;

class IncomeModel extends AgileModel {

    /**
     * 分别给用户收益
     * @param type $orderInfo
     * @param type $pay_user_id
     * @return boolean
     */
    public function payUserIncome($orderInfo, $pay_user_id) {
        if (empty($orderInfo) || !$pay_user_id) {
            return false;
        }

        $totalIncome = $orderInfo['total_fee'];

        # 1.获取付费内容的信息
        $inquireModel = new \Common\Model\InquireModel();
        $inquire = $inquireModel->getByPk($orderInfo['inquire_id']);

        # 2.解析分享的code
        $pay_sign_code = $inquireModel->decryptionShareHash($orderInfo['pay_sign_code']);
        $share = array();
        parse_str(str_replace('hash://?', '', $pay_sign_code), $share);
        $share_income_user = $share['suid'];

        # 1.2 先创建一条已付款的历史记录，保证看
        $viewHistoryAdd = array(
            'inquire_id' => $orderInfo['inquire_id'],
            'inquire_title' => $orderInfo['inquire_title'],
            'current_user_id' => $pay_user_id ? $pay_user_id : 0,
            'target_user_id' => $share_income_user ? $share_income_user : 0,
            'status' => HistoryModel::STATUS_VIEW,
            'hash_code' => $orderInfo['pay_sign_code'],
            'create_time' => time()
        );
        $historyModel = new \Common\Model\HistoryModel();
        $viewHistoryAddSta = $historyModel->add($viewHistoryAdd);

        # 3.开始给收益
        # 3.1 如果得到收益的用户（A）和付费的作者（B）是同一个人，则这个人独得100%的收益， 即：A==B，A得到100%
        # 3.2 如果得到收益的用户（A）和付费的作者（B）不是同一人，则得到收益的用户获取50%，作者获取30%，即：A!=B，A得到50%，B得到30%
        if ($share_income_user == $inquire['user_id']) {
            $incomeAdd = array(
                'inquire_id' => $orderInfo['inquire_id'],
                'order_id' => $orderInfo['id'],
                'income' => $totalIncome,
                'total_price' => $totalIncome,
                'income_user_id' => $share_income_user,
                'pay_user_id' => $pay_user_id,
                'remark' => '用户' . $share_income_user . '（作者） 得到付费内容“' . $inquire['title'] . '”100%收益',
                'create_time' => time()
            );
            $incomeId = $this->add($incomeAdd);
            return true;
        }


        # 3.2
        $shareUserAdd = array(
            'inquire_id' => $orderInfo['inquire_id'],
            'order_id' => $orderInfo['id'],
            'income' => round(($totalIncome * ($inquire['share_income'] / 100)), 2),
            'total_price' => $totalIncome,
            'income_user_id' => $share_income_user,
            'pay_user_id' => $pay_user_id,
            'remark' => '用户' . $share_income_user . ' 得到付费内容“' . $inquire['title'] . '”' . $inquire['share_income'] . '%收益',
            'create_time' => time()
        );
        $shareAddSta = $this->add($shareUserAdd);

        $authorAdd = array(
            'inquire_id' => $orderInfo['inquire_id'],
            'order_id' => $orderInfo['id'],
            'income' => round(($totalIncome * ($inquire['author_income'] / 100)), 2),
            'total_price' => $totalIncome,
            'income_user_id' => $inquire['user_id'],
            'pay_user_id' => $pay_user_id,
            'remark' => '用户' . $inquire['user_id'] . ' （作者）得到付费内容“' . $inquire['title'] . '”' . $inquire['author_income'] . '%收益',
            'create_time' => time()
        );
        $authorAddSta = $this->add($authorAdd);

        return true;
    }

}

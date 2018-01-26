<?php

/**
 * 历史付费内容的记录 
 */
namespace Common\Model;
use Think\Model;
use Common\Model\AgileModel;

class HistoryModel extends AgileModel {
    
	/**
	 * 定义状态
	 */
	const STATUS_PUBLISH = 0;
	const STATUS_VIEW = 1;
	const STATUS_SHARE = 2;
	
	static public $status = array(
		self::STATUS_PUBLISH => array(
			'kw' => "STATUS_PUBLISH",
			'desc' => "发布",
		),
		self::STATUS_VIEW => array(
			'kw' => "STATUS_VIEW",
			'desc' => "查看"
		),
        self::STATUS_SHARE => array(
			'kw' => "STATUS_SHARE",
			'desc' => "转发"
		)
	);
    
    
    /**
     * 计算历史记录中的付费查看人数
     * @param type $orderInfo
     * @param type $login_user_id
     * @return boolean
     */
    public function incrPayNum($orderInfo, $login_user_id) {
        if (empty($orderInfo) || !$login_user_id) {
            return false;
        }
        
        $inquireModel = new \Common\Model\InquireModel();
        $inquire = $inquireModel->getByPk($orderInfo['inquire_id']);
        
        $pay_sign_code = $inquireModel->decryptionShareHash($orderInfo['pay_sign_code']);
        $share = array();
        parse_str(str_replace('hash://?', '', $pay_sign_code), $share);
        $share_income_user = $share['suid'];
        
        # 1.递增分享记录
        $shareCond = array(
            'inquire_id' => $orderInfo['inquire_id'],
            'current_user_id' => $login_user_id,
            'target_user_id' => $share_income_user,
            'status' => self::STATUS_SHARE
        );
        $findRow = $this->where($shareCond)->find();
        if ($findRow) {
            $up = array(
                'id' => $findRow['id'],
                'pay_num' => $findRow['pay_num'] + 1
            );
            $this->save($up);
        }
        
        
        # 2.递增作者记录
        $authorCond = array(
            'inquire_id' => $orderInfo['inquire_id'],
            'current_user_id' => $inquire['user_id'],
            'status' => self::STATUS_PUBLISH
        );
        $authorFindRow = $this->where($authorCond)->find();
        if ($authorFindRow) {
            $up = array(
                'id' => $authorFindRow['id'],
                'pay_num' => $authorFindRow['pay_num'] + 1
            );
            $this->save($up);
        }
        
        return true;
    }
    
    
}


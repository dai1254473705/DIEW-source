<?php

/**
 * 付费内容的数据模型 
 */
namespace Common\Model;
use Think\Model;
use Common\Model\AgileModel;

class InquireModel extends AgileModel {
    
	/**
	 * 定义是否显示
	 */
	const IS_ALLOW_SHARE_TRUE = 1;
	const IS_ALLOW_SHARE_FALSE = 0;
	
	static public $is_allow_share = array(
		self::IS_ALLOW_SHARE_TRUE => array(
			'kw' => "IS_ALLOW_SHARE_TRUE",
			'desc' => "是"
		),
		self::IS_ALLOW_SHARE_FALSE => array(
			'kw' => "IS_ALLOW_SHARE_FALSE",
			'desc' => "否"
		),
	);
    
    
    /**
     * 检测用户是否能够查看content的内容
     * @param type $current_user_id     当前操作用户ID
     * @param type $inquire_id          付费内容ID
     * @return boolean
     */
    public function checkContentIsShow($current_user_id, $inquire_id) {
        if (!$current_user_id && !$inquire_id) {
            return false;
        }
        
        $inquireDetail = $this->getByPk($inquire_id);
        
        # 开始判断，当前用户是发布者
        if ($current_user_id == $inquireDetail['user_id']) {
            return true;
        }
        
        $historyModel = new \Common\Model\HistoryModel();
        $cond = array(
            'inquire_id' => $inquire_id,
            'current_user_id' => $current_user_id,
            'status' => HistoryModel::STATUS_VIEW   //查同一个 inquire_id 在历史记录中是否存在 被当前用户 付款的，存在则能看，否则不能看
        );
        $exists = $historyModel->where($cond)->count();
        return $exists ? true : false;
    }
    
    
    /**
     * 生成用户的加密分享HASH
     * @param type $current_user_id
     * @param type $inquire_id
     * @return string
     */
    public function generateShareHash($current_user_id, $inquire_id) {
        if (!$current_user_id || !$inquire_id) {
            return '';
        }
        $hashUrl = 'hash://?suid=' . $current_user_id . '&inid=' . $inquire_id;
        $mapHash = encryption(encryption($hashUrl));
        return $mapHash;
    }
    
    
    /**
     * 解密 share hash code 的内容
     * @param type $hashCode
     * @return string
     */
    public function decryptionShareHash($hashCode) {
        if (!$hashCode) {
            return '';
        }
        return encryption(encryption($hashCode, false), false);
    }
    
}


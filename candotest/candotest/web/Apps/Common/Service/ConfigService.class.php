<?php

/**
 * 配置信息外部调用类
 * @Todo 用于获取配置信息，统一管理
 */

namespace Common\Service;

use Think\Model;

class ConfigService extends Model {
    
    /**
     * 获取连续签到赠送礼物的配置文件
     * @return boolean
     */
    static public function signRule() {
        $rawRule = require APP_PATH . 'Common/Conf/Data/sign_rule.data.php';
        $res = array();
        foreach ($rawRule as $value) {
            $res[$value['key']] = $value['value'];
        }
        return $res;
    }
    
    /**
     * 获取签到礼物的配置文件
     * @return type
     */
    static public function giftData(){
        return require APP_PATH . 'Common/Conf/Data/sign_gift.data.php';
    }
    
    
    /**
     * 获取签到礼物的配置文件
     * @return type
     */
    static public function signRawRule(){
        return require APP_PATH . 'Common/Conf/Data/sign_rule.data.php';
    }
    
    

}

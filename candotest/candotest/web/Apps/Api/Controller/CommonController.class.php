<?php

/**
 * 接口基础继承类
 */

namespace Api\Controller;

use Think\Controller;

class CommonController extends \Think\Controller {

    public $params = array();
    public $rootDomain;
    static public $fsEnv;
    static public $uid = 0;
    static public $userInfo = array();

    // 初始化控制器时会调用的方法
    public function _initialize($checkToken = true) {
        
        # 获取域名信息
        $this->rootDomain = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
        
        # 全局验证，获取参数，使用合并方式，主要为了去除$_REQUEST中不需要的key
        list($get, $post) = array(I('get.'), I('post.'));
        $this->params = array_merge($get, $post);
        if ($this->isBase64($this->params['params'])) {
            $params = base64_decode($this->params['params']);
            $this->params = json_decode($params, true);
        }
        
        # 验证token
        if ($checkToken) {
            $this->checkToken();
            
        } else {
            
            # 不验证token时，获取用户信息
            $token = htmlspecialchars_decode($this->params['token']);
            $token = trim($token, '"');
            if ($token) {
                $userModel = new \Common\Model\UserModel();
                $uid = $userModel->getUidByToken($token);
                self::$uid = $uid;
                self::$userInfo = $userModel->getUserByUid(self::$uid);
            }
        }
    }
    
    /**
     * 检测token
     */
    public function checkToken() {
        $userModel = new \Common\Model\UserModel();
        $token = htmlspecialchars_decode($this->params['token']);
        $token = trim($token, '"');
        $uid = $userModel->getUidByToken($token);
        if (!$uid) {
            $this->fail_exit('身份验证失败，请求无效');
        }
        self::$uid = $uid;
        self::$userInfo = $userModel->getUserByUid(self::$uid);
    }

    
    /**
     * ajax返回
     * @param type $data
     * @param type $msg
     * @return type
     */
    public function success_exit($data = array(), $msg = null) {
        
        $return = array(
            'status' => true,
            'message' => $msg ? $msg : '操作成功',
            'data' => empty($data) ? array() : $data
        );
        
        return $this->ajaxReturn($return, 'json');
    }

    /**
     * ajax错误返回
     * @param type $errcode
     * @param type $msg
     * @return type
     */
    public function fail_exit($msg = null, $data = array()) {

        $return = array(
            'status' => false,
            'message' => $msg ? $msg : '操作失败，请重试',
            'data' => $data ? $data : array()
        );
        
        return $this->ajaxReturn($return, 'json');
    }
    
    
    /**
     * 判断字符串是否经过编码方法
     * @param type $str
     * @return boolean
     */
    public function isBase64($str) {
        if ($str == base64_encode(base64_decode($str))) {
            return true;
        } else {
            return false;
        }
    }
    

}

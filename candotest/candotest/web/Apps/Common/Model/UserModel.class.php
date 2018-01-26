<?php

/**
 * 用户的数据模型
 */

namespace Common\Model;

use Think\Model;
use Common\Model\AgileModel;

class UserModel extends AgileModel {

    protected $tableName = 'user';
    protected $pk = "id";

    /**
     * 定义状态
     */
    const STATUS_TRUE = 1;
    const STATUS_FALSE = 0;

    static public $status = array(
        self::STATUS_TRUE => array(
            'kw' => "STATUS_TRUE",
            'desc' => "正常"
        ),
        self::STATUS_FALSE => array(
            'kw' => "STATUS_FALSE",
            'desc' => "禁用"
        ),
    );

    /**
     * 定义sex
     */
    const SEX_MALE = 1;
    const SEX_FEMALE = 0;

    static public $sex = array(
        self::SEX_MALE => array(
            'kw' => "SEX_MALE",
            'desc' => "男"
        ),
        self::SEX_FEMALE => array(
            'kw' => "SEX_FEMALE",
            'desc' => "女"
        ),
    );

    /**
     * 创建用户token
     * @param type $uid
     * @return boolean
     */
    public function createUserToken($uid) {
        if (!$uid) {
            return false;
        }
        $token = genToken();
        $up = array(
            'id' => $uid,
            'token' => $token
        );
        $ups = $this->save($up);
        if (!$ups) {
            return false;
        }

        return $this->getUserByToken($token);
    }

    /**
     * 根据uid获取用户信息
     * @param type $where
     * @param type $limit
     * @return type
     */
    public function getUserByUid($id) {
        $user = $this->getByPk($id);
        return $this->filterField($user);
    }

    /**
     * 根据uid获取用户信息
     * @param type $where
     * @param type $limit
     * @return type
     */
    public function getUserByUidNoToken($id) {
        $user = $this->getByPk($id);
        unset($user['token']);
        return $this->filterField($user);
    }

    /**
     * 根据token获取用户信息
     * @param type $where
     * @param type $limit
     * @return type
     */
    public function getUserByToken($token) {
        $cond = array(
            'token' => $token
        );
        $user = $this->where($cond)->find();

        return $this->filterField($user);
    }

    /**
     * 过滤返回字段
     * @param type $data
     * @return type
     */
    public function filterField($data) {
        if (empty($data)) {
            return array();
        }
        $filter = array('openid', 'unionid', 'groupid', 'extend', 'status', 'remark');
        $formatTime = array('create_time');
        $re = array();
        foreach ($data as $key => $value) {
            if (!in_array($key, $filter)) {
                if (in_array($key, $formatTime)) {
                    $re[$key] = date('Y-m-d H:i:s', $value);
                } else {
                    $re[$key] = $value;
                }
            }
        }

        return $re;
    }

    /**
     * 根据条件获取用户总数
     * @param type $where
     * @return type
     */
    public function getUserCountByCond($where) {
        $count = $this->where($where)->count();
        return $count ? (int) $count : 0;
    }

    /**
     * 根据用户id，获取用户名
     * @param type $uid
     * @return type
     */
    public function getNameByUid($uid) {
        return $this->where("id={$uid}")->getField("nickname");
    }

    /**
     * 根据用户token，获取用户uid
     * @param type $token
     * @return type
     */
    public function getUidByToken($token) {
        return $this->where("token='{$token}'")->getField($this->pk);
    }

    /**
     * 根据用户openid，获取用户UID
     * @param type $openid
     * @return type
     */
    public function getUidByOpenId($openid) {
        return $this->where("openid='{$openid}'")->getField($this->pk);
    }
    
    /**
     * 根据用户UID，获取openid
     * @param type $user_id
     * @return type
     */
    public function getOpenIdByUid($user_id) {
        return $this->where("id={$user_id}")->getField('openid');
    }

    /**
     * 根据用户id，获取用户头像
     * @param type $uid
     * @return type
     */
    public function getAvatarByUid($uid) {
        return $this->where("id={$uid}")->getField("headimgurl");
    }

}

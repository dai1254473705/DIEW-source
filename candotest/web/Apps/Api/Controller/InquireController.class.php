<?php

/**
 * 付费查询创建类
 */

namespace Api\Controller;

use Think\Controller;

class InquireController extends CommonController {
    
    static $oneDayPubTime = 3;
    static $enableShare = true;
    
    /**
     * 一天内创建付费查询的次数
     */
    public function pub_total() {
        
        # 提交时检测用户是否登录
        $this->checkToken();
        
        $inquireModel = new \Common\Model\InquireModel();
        $startTime = strtotime(date('Y-m-d 00:00:00'));
        $endTime = $startTime + 86400;
        $where = array(
            'user_id' => self::$uid,
            'create_time' => array('between', $startTime . ',' . $endTime)
        );
        $count = $inquireModel->where($where)->count();
        if ($count > self::$oneDayPubTime) {
            $this->success_exit(array(
                'total' => 0,
                'enable_share' => self::$enableShare
            ));
        }
        
        $this->success_exit(array(
            'total' => self::$oneDayPubTime - $count,
            'enable_share' => self::$enableShare
        ));
    }
    
    
    /**
     * 创建付费查询
     */
    public function add() {
        
        # 提交时检测用户是否登录
        $this->checkToken();
        
        # 验证是否可以在发布
        $inquireModel = new \Common\Model\InquireModel();
        $startTime = strtotime(date('Y-m-d 00:00:00'));
        $endTime = $startTime + 86400;
        $where = array(
            'user_id' => self::$uid,
            'create_time' => array('between', $startTime . ',' . $endTime)
        );
        $count = $inquireModel->where($where)->count();
        if ($count >= self::$oneDayPubTime) {
            $this->fail_exit('今天不能再发布了哦~');
        }
        
        $title = $this->params['title'];
        $price = $this->params['price'];
        $content = $this->params['content'];
        $is_share = $this->params['is_share'];
        if (!$title || !$price || !$content) {
            $this->fail_exit('请求参数不完整');
        }
        
        if (!self::$uid) {
            $this->fail_exit('请同意授权后再操作');
        }
        
        $add = array(
            'title' => $title,
            'price' => $price,
            'content' => $content,
            'user_id' => self::$uid,
            'is_allow_share' => $is_share ? \Common\Model\InquireModel::IS_ALLOW_SHARE_TRUE : \Common\Model\InquireModel::IS_ALLOW_SHARE_FALSE,
//            'is_allow_share' => \Common\Model\InquireModel::IS_ALLOW_SHARE_TRUE,
            'share_income' => 50,   //转发者所得收入（价格的百分比）
            'author_income' => 30,  //被转发时作者所得收入
            'create_time' => time()
        );
        
        $model = new \Common\Model\InquireModel();
        $inID = $model->add($add);
        if (!$inID) {
            $this->fail_exit('创建付费内容失败');
        }
        
        #### 同时创建一个历史记录
        $historyAdd = array(
            'inquire_id' => $inID,
            'inquire_title' => $title,
            'current_user_id' => self::$uid,
            'target_user_id' => 0,
            'status' => \Common\Model\HistoryModel::STATUS_PUBLISH,
            'create_time' => time(),            
        );
        $historyModel = new \Common\Model\HistoryModel();
        $historyId = $historyModel->add($historyAdd);
        
        $this->success_exit(array('inquire_id' => $inID), '创建成功');
    }
    
    
    /**
     * 获取付费查询的详细信息
     */
    public function detail() {
        
        $this->checkToken();
        
        $inquire_id = $this->params['inid'];
        if (!$inquire_id) {
            $this->fail_exit('请选择要查看的付费内容');
        }
        
        #### 解析携带的hash_code信息
        $hash_code = urldecode($this->params['sh']);
        $decodeHashCode = encryption(encryption($hash_code, false), false);
        $hashArray = array();
        if ($decodeHashCode) {
            $decodeHashCode = str_replace('hash://?', '', $decodeHashCode);
            parse_str($decodeHashCode, $hashArray);
        }
        
        # 获取付费内容的详细信息
        $inquireModel = new \Common\Model\InquireModel();
        $detail = $inquireModel->getByPk($inquire_id);
        if (!$detail) {
            $this->fail_exit('数据异常');
        }
        
        # 根据token，判断是否可查看内容的详细信息
        $isShow = $inquireModel->checkContentIsShow(self::$uid, $inquire_id);
        $detail['is_show'] = $isShow;
        if (!$isShow) {
            unset($detail['content']);
        }
        
        # 根据传入的参数，返回分享时需要携带的身份识别加密参数
        $share_hash = $inquireModel->generateShareHash(self::$uid, $inquire_id);
        $detail['hash_code'] = $share_hash; //分享码
        
        
        /**
         * 判断状态，是否需要[新增/修改]一个“转发”的状态
         * 需要满足条件1：当前登录用户不是分享者；
         * 需要满足条件2：当前登录用户不是作者；
         * 满足以上两个条件，创建一个分享的历史记录
         */
        if ($hashArray) {
            if ((self::$uid != $hashArray['suid']) && (self::$uid != $detail['user_id'])) {
                $historyModel = new \Common\Model\HistoryModel();
                $historyCond = array(
                    'inquire_id' => $inquire_id,
                    'current_user_id' => self::$uid,
                    'status' => \Common\Model\HistoryModel::STATUS_SHARE
                );
                $isExists = $historyModel->where($historyCond)->find();

                if (!$isExists) {
                    $historyCond['target_user_id'] = $hashArray['suid'];
                    $historyCond['inquire_title'] = $detail['title'] ? $detail['title'] : '';
                    $historyCond['hash_code'] = $hash_code;
                    $historyCond['create_time'] = time();
                    $historyIns = $historyModel->add($historyCond);
                } else {
                    $historyCond['inquire_title'] = $detail['title'] ? $detail['title'] : '';
                    $historyCond['update_time'] = time();
                    $historyCond['id'] = $isExists['id'];
                    $historyUps = $historyModel->save($historyCond);
                }

                # 赋值变量（客户端是否需要保存分享者的识别号，用于支付时算金额）
                $detail['is_sign_code'] = true;
                $detail['sign_code'] = $hash_code; //转发识别码
            }
        }
        
        
        $this->success_exit($detail);
    }
    
}

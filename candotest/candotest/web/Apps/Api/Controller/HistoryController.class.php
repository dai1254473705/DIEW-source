<?php

/**
 * 历史付费查询
 */

namespace Api\Controller;

use Think\Controller;

class HistoryController extends CommonController {
    
    /**
     * 历史信息
     */
    public function index() {
        
        $param = $this->params;
        $historyModel = new \Common\Model\HistoryModel();
        
        $where = array(
            'current_user_id' => self::$uid,
        );
        
        //查询数据，分页
		$per = 10;
		$count = $historyModel->where($where)->count();
        if (!$count) {
            $this->fail_exit('暂无更多数据');
        }
        
        $status = \Common\Model\HistoryModel::$status;
		$page = new \Components\Page($count, $per, $param);
		$limit = $page->firstRow . ',' . $page->listRows;
		$list = $historyModel->where($where)->order('id desc')->limit($limit)->select();
        $classNames = array('','blue','green');
        if (!$list) {
            $this->fail_exit('暂无更多数据');
        }
        
        foreach ($list as $k => $v) {
            $v['statusText'] = $status[$v['status']]['desc'];
            $v['statusClass'] = $classNames[$v['status']];
            $list[$k] = $v;
        }
        
        $pageData = $page->style(); //调用分页的上下页链接
        
        $ajaxUrl = $pageData['ajax_url'];
        
        $result = array(
            'list' => $list,
            'ajax_url' => $ajaxUrl
        );
        
        $this->success_exit($result);
    }
    
}

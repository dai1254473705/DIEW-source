<?php

/**
 * cms模块数据模型 
 */

namespace Common\Model;

use Think\Model;
use Common\Model\AgileModel;

class CmsElementModel extends AgileModel {

    /**
     * 定义状态
     */
    const STATUS_TRUE = 1;
    const STATUS_FALSE = 0;

    static public $status = array(
        self::STATUS_TRUE => array(
            'kw' => "STATUS_TRUE",
            'desc' => "显示"
        ),
        self::STATUS_FALSE => array(
            'kw' => "STATUS_FALSE",
            'desc' => "隐藏"
        ),
    );
    
}

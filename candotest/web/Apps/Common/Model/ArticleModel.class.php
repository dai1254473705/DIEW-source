<?php

/**
 * 文章数据模型 
 */
namespace Common\Model;
use Think\Model;
use Common\Model\AgileModel;

class ArticleModel extends AgileModel {
    
	/**
	 * 定义是否显示
	 */
	const IS_OPEN_TRUE = 1;
	const IS_OPEN_FALSE = 0;
	
	static public $is_open_status = array(
		self::IS_OPEN_TRUE => array(
			'kw' => "IS_OPEN_TRUE",
			'desc' => "显示"
		),
		self::IS_OPEN_FALSE => array(
			'kw' => "IS_OPEN_FALSE",
			'desc' => "隐藏"
		),
	);
    
}


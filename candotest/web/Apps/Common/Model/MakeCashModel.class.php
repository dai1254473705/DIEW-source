<?php

/**
 * 提现 数据模型 
 */

namespace Common\Model;

use Think\Model;
use Common\Model\AgileModel;

class MakeCashModel extends AgileModel {

    /**
	 * 定义提现状态
	 */
	const STATUS_PROGREING = 0;
	const STATUS_FINISHED = 1;
	
	static public $status = array(
		self::STATUS_PROGREING => array(
			'kw' => "STATUS_PROGREING",
			'desc' => "处理中"
		),
		self::STATUS_FINISHED => array(
			'kw' => "STATUS_FINISHED",
			'desc' => "已提现"
		),
	);

}

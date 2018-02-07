<?php

/**
 * 订单的数据模型 
 */
namespace Common\Model;
use Think\Model;
use Common\Model\AgileModel;

class OrderModel extends AgileModel {
    
	/**
	 * 定义支付状态
	 */
	const STATUS_UNPAID = 0;
	const STATUS_PAID = 1;
	
	static public $status = array(
		self::STATUS_UNPAID => array(
			'kw' => "STATUS_UNPAID",
			'desc' => "未支付"
		),
		self::STATUS_PAID => array(
			'kw' => "STATUS_PAID",
			'desc' => "已支付"
		),
	);
    
}


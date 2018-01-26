<?php

/**
 * MongoDB操作类，重写ThinkPHP的MongoModel
 * 
 * 目的：统一设置连接集合信息，为 增、删、改 提供便捷
 * 
 * 注意：继承AgileModel之后，调用其中的方法，model不能使用系统自带的方法实例化，需要直接new
 * 
 */

namespace Common\Model;

use Think\Model\MongoModel;

class MongoDBModel extends MongoModel {

    protected $dbName = "DB_NAME";
    
    protected $connection = array(
        'DB_TYPE' => 'Mongo', // 数据库类型
        'DB_HOST' => '127.0.0.1', // 服务器地址
        'DB_USER' => 'root', // 用户名
        'DB_PWD' => 'KL+nX3(igH]QBX3(igH{MB^5a(9', // 密码
        'DB_PORT' => 27017, // 端口
        'DB_DEBUG' => true,
        'DB_PREFIX' => 'gui_', // 数据库表前缀
    );
    
}

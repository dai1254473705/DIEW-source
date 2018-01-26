<?php

return array(
    
    'ENCRYPTION_KEY' => 'iUoYu23z8njUkeL',
    
    // 路由配置
    'URL_ROUTER_ON' => true,
    'URL_ROUTE_RULES' => array(
        
        // 用户登录信息
        'login' => array('User/verify', array()),
        'query/pub_total' => array('Inquire/pub_total', array()),
        'query/add' => array('Inquire/add', array()),
        'query/detail' => array('Inquire/detail', array()),
        'history' => array('History/index', array()),
        'my' => array('User/my', array()),
        'payment' => array('Payment/create', array()),
        'wechat/server_callback' => array('Payment/serverCallback', array()),
        'wechat/callback' => array('Payment/callback', array()),
        'make_cash' => array('User/makeCash', array()),
        
    ),
);

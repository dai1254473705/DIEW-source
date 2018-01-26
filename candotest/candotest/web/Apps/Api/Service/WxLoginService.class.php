<?php

/**
 * 微信登录辅助类
 * @Todo 
 *  1.用于获取用户的session_key和openid
 *  2.对用户信息进行校验操作
 */

namespace Api\Service;

use Think\Model;

class WxLoginService extends Model {

    static private $appId = 'wx7590cffee6387a75';
    static private $appSecret = '9a0003e7f6b72a1ef2b60b73d35beeab';

    /**
     * 发送请求，获取session_key 和 openid信息
     * @param type $code
     * @return boolean
     */
    static public function WxJsCodeToSession($code) {
        if (!$code) {
            return false;
        }
        
        $wxRequestUrl = 'https://api.weixin.qq.com/sns/jscode2session';
        $bulidQuery = array(
            'appid' => self::$appId,
            'secret' => self::$appSecret,
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        );
        
        $url = $wxRequestUrl . '?' . http_build_query($bulidQuery);
        
        # 开始请求
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false, // 跟随跳转
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_HEADER => 0,
            CURLOPT_NOBODY => 0,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => array(),
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
            ),
            CURLOPT_REFERER => $referer,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2
        ));
        
        # 一次请求
        $response = curl_exec($curl);

        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return false;
        }
        
        if (!$response) {
            return false;
        }
        
        $responseArray = json_decode($response, true);
        return $responseArray;
    }

}

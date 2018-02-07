<?php

// +----------------------------------------------------------------------
// | 项目定义函数库
// +----------------------------------------------------------------------
// | Author: PuLinGao <qianxunpl726@163.com>
// +----------------------------------------------------------------------

/**
 * 项目内数据加密解密函数
 * 算法：使用异或的方式加密，定义一个固定的key，加密内容与key 取异或，再base64加密，返回
 * @param string $msg		加密的内容，以字符串的形式
 * @param boolean $type		true：加密（默认true） false：解密
 * @return type				返回值，返回加密后的字符串，用于存储
 */
function encryption($msg, $type = true) {
    if (!$msg) {
        return '';
    }
    $encryKey = C('ENCRYPTION_KEY') ? C('ENCRYPTION_KEY') : 'iUoYu23z8njUkeL';
    // 如果是解密，先base解密
    if (!$type) {
        $msg = base64_decode($msg);
    }

    //防止加密key的长度不够
    $msgLength = strlen($msg);
    $encryKeyLength = strlen($encryKey);
    if ($msgLength > $encryKeyLength) {
        $encryKey = str_repeat($encryKey, ceil($msgLength / $encryKeyLength));
    }

    if ($type) {
        $return = base64_encode($encryKey ^ $msg);
    } else {
        $return = $encryKey ^ $msg;
    }
    return $return;
}

/**
 * 判断客户端类型，移动端或者电脑端
 */
function isMobile() {
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset($_SERVER['HTTP_VIA'])) {
        //找不到为flase,否则为true
        if (stristr($_SERVER['HTTP_VIA'], "wap")) {
            return true;
        }
    }
    //判断手机发送的客户端标志,兼容性有待提高
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array(
            'nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile',
            'phone',
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    //协议法，因为有可能不准确，放到最后判断
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

/**
 * 判断微信环境
 * @return boolean
 */
function isWeixin() {
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    }
    return false;
}

/**
 * 生成随机字符串
 * @param type $stringType		字符串类型，默认true，字符串类型，false，纯数字类型
 * @param type $length			随机字符串长度
 * @param type $perBloab		随机字符串每个分割块的长度
 * @param type $delimiter		分隔符，默认“-”
 * @return type
 */
function createRandString($stringType = true, $length = 12, $perBloab = 4, $delimiter = '-') {
    $stringStr = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $numStr = "0123456789";
    $str = $stringType ? $stringStr : ($stringStr . $numStr);

    $rand = '';
    for ($j = 1; $j <= $length; $j++) {
        $rand .= substr($str, mt_rand(0, strlen($str) - 1), 1);
        if (!($j % $perBloab)) {
            $rand .= $delimiter;
        }
    }

    return rtrim($rand, $delimiter);
}

/**
 * 生成Token
 */
function genToken() {
    return substr(md5(createRandString(true, 12, 4, '')), 4, 16);
}

/**
 * 将数组的String类型的值，转化为Number类型
 * @param type $array
 * @return type
 */
function arrayValueStr2Number($array) {
    if (empty($array)) {
        return array();
    }

    return array_map(function($mapV) {
        if (ctype_digit($mapV)) {
            $mapV = (int) $mapV;
        }
        if (is_numeric($mapV) && is_float($mapV * 1)) {
            $mapV = (float) $mapV;
        }
        if (is_int($mapV)) {
            $mapV = (int) $mapV;
        }
        return $mapV;
    }, $array);
}

/**
 * 将数组的值，全部转换为小写
 * @param type $array
 * @return type
 */
function arrayValueToLower($array) {
    if (empty($array)) {
        return array();
    }

    return array_map(function($mapV) {
        return strtolower($mapV);
    }, $array);
}

/**
 * 获取文件列表
 * @param type $dir
 * @return type
 */
function listDir($dir) {
    $result = array();
    if (is_dir($dir)) {
        $file_dir = scandir($dir);
        foreach ($file_dir as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            } elseif (is_dir($dir . $file)) {
                $result = array_merge($result, listDir($dir . $file . '/'));
            } else {
                array_push($result, $dir . DIRECTORY_SEPARATOR . $file);
            }
        }
    }
    return $result;
}

/**
 * 根据语言，获取对应数据的值
 * @param type $data
 * @param type $name
 * @return type
 */
function alm($data, $name, $switch = false) {
    if (!defined('API_LANG_SET')) {
        return lm($data, $name, $switch);
    }

    $suf = (API_LANG_SET == "cn") ? "" : "_" . API_LANG_SET;
    $nameReal = $name . $suf;
    if (!isset($data[$nameReal])) {
        return $data[$name];
    }
    return $switch ? htmlspecialchars_decode($data[$nameReal]) : $data[$nameReal];
}

/**
 * 根据语言，获取对应数据的值
 * @param type $data
 * @param type $name
 * @return type
 */
function lm($data, $name, $switch = false) {
    $langKey = \Common\Model\LanguageModel::getKey(LANG_SET);
    $suf = '';
    switch ($langKey) {
        case \Common\Model\LanguageModel::LANG_ZH_CN:
            $suf = '';
            break;
        case \Common\Model\LanguageModel::LANG_EN_US:
            $suf = '_en';
            break;
        case \Common\Model\LanguageModel::LANG_ZH_HK:
            $suf = '_hk';
            break;
        default:
            break;
    }
    $nameReal = $name . $suf;
    if (!isset($data[$nameReal])) {
        return $data[$name];
    }
    return $switch ? htmlspecialchars_decode($data[$nameReal]) : $data[$nameReal];
}

/**
 * web友好时间戳
 * @param type $the_time
 * @return type
 */
function web_time_tran($the_time) {
    $t = time() - $the_time;

    $names = array(
        'year' => array(
            'zh-cn' => '年',
            'en-us' => ' Year',
            'zh-hk' => '年',
        ),
        'month' => array(
            'zh-cn' => '月',
            'en-us' => ' Month',
            'zh-hk' => '月',
        ),
        'week' => array(
            'zh-cn' => '星期',
            'en-us' => ' Week',
            'zh-hk' => '星期',
        ),
        'day' => array(
            'zh-cn' => '天',
            'en-us' => ' Days',
            'zh-hk' => '天',
        ),
        'hour' => array(
            'zh-cn' => '小时',
            'en-us' => ' Hour',
            'zh-hk' => '小時',
        ),
        'minute' => array(
            'zh-cn' => '分钟',
            'en-us' => ' Minute',
            'zh-hk' => '分鐘',
        ),
        'second' => array(
            'zh-cn' => '秒',
            'en-us' => ' Second',
            'zh-hk' => '秒',
        )
    );
    $ago = array('zh-cn' => '前', 'en-us' => ' Ago', 'zh-hk' => '前');
    $f = array(
        '31536000' => 'year',
        '2592000' => 'month',
        '604800' => 'week',
        '86400' => 'day',
        '3600' => 'hour',
        '60' => 'minute',
        '1' => 'second'
    );

    foreach ($f as $k => $v) {
        $c = floor($t / (int) $k);
        if (0 != $c) {
            $vv = $names[$v][LANG_SET];
            return $c . $vv . $ago[LANG_SET];
        }

        if ($v == "second") {
            $vv = $names[$v][LANG_SET];
            return 1 . $vv . $ago[LANG_SET];
        }
    }
}

/**
 * web友好时间戳
 * @param type $the_time
 * @return type
 */
function web_time_tran_single($the_time) {
    $t = time() - $the_time;

    $names = array(
        'year' => '年',
        'month' => '月',
        'week' => '星期',
        'day' => '天',
        'hour' => '小时',
        'minute' => '分钟',
        'second' => '秒'
    );
    $f = array(
        '31536000' => 'year',
        '2592000' => 'month',
        '604800' => 'week',
        '86400' => 'day',
        '3600' => 'hour',
        '60' => 'minute',
        '1' => 'second'
    );
    $ago = '前';
    foreach ($f as $k => $v) {
        $c = floor($t / (int) $k);
        if (0 != $c) {
            $vv = $names[$v];
            return $c . $vv . $ago;
        }

        if ($v == "second") {
            if ($c == 0) {
                return '刚刚';
            }
            $vv = $names[$v];
            return 1 . $vv . $ago;
        }
    }
}

/**
 * gui app 友好时间戳
 * @param type $the_time
 * @return type
 */
function web_time_tran_gui($the_time) {
    $t = time() - $the_time;

    $names = array(
        'year' => '年',
        'day' => '天',
        'hour' => '小时',
        'minute' => '分钟',
        'second' => '秒'
    );
    $f = array(
        '31536000' => 'year',
        '86400' => 'day',
        '3600' => 'hour',
        '60' => 'minute',
        '1' => 'second'
    );
    $ago = '前';
    foreach ($f as $k => $v) {
        $c = floor($t / (int) $k);

        # 正常存在值时
        if ($c != 0) {

            # 年
            if ($v == 'year') {
                return date('Y-m-d', $the_time);
            }

            # 天
            if ($v == 'day') {

                $firstDayTime = strtotime(date('Y') . '-01-01');
                if ($the_time < $firstDayTime) {
                    return date('Y-m-d', $the_time);
                }

                return date('m-d', $the_time);
            }

            # 正常返回
            $vv = $names[$v];
            return $c . $vv . $ago;
        }


        ########## 单独处理 #############
        # 秒
        if ($v == "second") {
            if ($c == 0) {
                return '刚刚';
            }

            # 正常返回
            $vv = $names[$v];
            return $c . $vv . $ago;
        }
    }
}

/**
 * 解析图片地址，替换为cdn地址
 * @param type $path
 * @return type
 */
function parseCdnUrl($path) {
    $cdnHost = C('CDN_DOMAIN');
    if (!$path) {
        return "";
    }

    # 兼容老数据
    $oldCdnHost = 'img.gui66.com';
    if (false !== stripos($path, $oldCdnHost)) {
        return $path;
    }

    if (false !== stripos($path, $cdnHost)) {
        return $path;
    }
    preg_match_all('/.*(Uploads\/.*)/', $path, $matches);

    # 没有匹配上
    if (!$matches[1][0]) {

        # 开始匹配Public
        preg_match_all('/.*(Public\/.*)/', $path, $match);
        if (!$match[1][0]) {

            # 开始匹配assets
            preg_match_all('/.*(assets\/.*)/', $path, $assMatch);
            if ($assMatch[1][0]) {
                return $cdnHost . '/Public/' . ltrim($assMatch[1][0], "/");
            }

            return $cdnHost . $path;
        }

        return $cdnHost . '/' . ltrim($match[1][0], "/");
    }

    return $cdnHost . '/Public/' . ltrim($matches[1][0], "/");
}

/**
 * 解析兼容数据的图片地址
 * @param type $path
 * @return string
 */
function parseOldImgUrl($path) {
    if (!$path) {
        return '';
    }

    $host = 'http://img.gui66.com';
    $path = ltrim($path, "/");
//    $urlencodePath = urlencode($path);

    $orgUrl = $host . '/' . $path;

    return $orgUrl;
}

/**
 * 解析Home模块中的图片问题
 * @param type $path
 * @return type
 */
function parseHomeCdnUrl($path) {
    $cdnHost = C('CDN_DOMAIN');
    if (!$path) {
        return "";
    }

    # 兼容老数据
    $oldCdnHost = 'img.gui66.com';
    if (false !== stripos($path, $oldCdnHost)) {
        return $path;
    }

    if (false !== stripos($path, $cdnHost)) {
        return $path;
    }

    preg_match_all('/.*(Uploads\/.*)/', $path, $matches);

    # 没有匹配上
    if (!$matches[1][0]) {

        # 开始匹配Public
        preg_match_all('/.*(Public\/.*)/', $path, $match);
        if (!$match[1][0]) {

            # 开始匹配assets
            preg_match_all('/.*(assets\/.*)/', $path, $assMatch);
            if ($assMatch[1][0]) {
                return '/Public/' . ltrim($assMatch[1][0], "/");
            }

            return $path;
        }

        return '/' . ltrim($match[1][0], "/");
    }

    return '/Public/' . ltrim($matches[1][0], "/");
}

/**
 * 求两个已知经纬度之间的距离,单位为米
 * @param $lngSrc, $lngDst 经度
 * @param $latSrc, $latDst 纬度
 * @return float 距离，单位米
 * */
function getdistance($lngSrc, $latSrc, $lngDst, $latDst, $isKm = false) {//根据经纬度计算距离
    //将角度转为狐度 
    $radLat1 = deg2rad($latSrc);
    $radLat2 = deg2rad($latDst);
    $radLng1 = deg2rad($lngSrc);
    $radLng2 = deg2rad($lngDst);
    $a = $radLat1 - $radLat2; //两纬度之差,纬度<90
    $b = $radLng1 - $radLng2; //两经度之差纬度<180
    $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
    if ($isKm) {
        return round($s / 1000, 2);
    }
    return $s;
}

/**
 * 友好时间戳
 * @param type $the_time
 * @return type
 */
function time_tran($the_time) {
    if (!defined('API_LANG_SET')) {
        return web_time_tran($the_time);
    }

    $t = time() - $the_time;

    $names = array(
        'year' => array(
            'cn' => '年',
            'en' => ' Year',
            'hk' => '年',
        ),
        'month' => array(
            'cn' => '月',
            'en' => ' Month',
            'hk' => '月',
        ),
        'week' => array(
            'cn' => '星期',
            'en' => ' Week',
            'hk' => '星期',
        ),
        'day' => array(
            'cn' => '天',
            'en' => ' Days',
            'hk' => '天',
        ),
        'hour' => array(
            'cn' => '小时',
            'en' => ' Hour',
            'hk' => '小時',
        ),
        'minute' => array(
            'cn' => '分钟',
            'en' => ' Minute',
            'hk' => '分鐘',
        ),
        'second' => array(
            'cn' => '秒',
            'en' => ' Second',
            'hk' => '秒',
        )
    );
    $ago = array('cn' => '前', 'en' => ' Ago', 'hk' => '前');
    $f = array(
        '31536000' => 'year',
        '2592000' => 'month',
        '604800' => 'week',
        '86400' => 'day',
        '3600' => 'hour',
        '60' => 'minute',
        '1' => 'second'
    );
    foreach ($f as $k => $v) {
        $c = floor($t / (int) $k);
        if (0 != $c) {
            $vv = $names[$v][API_LANG_SET];
            return $c . $vv . $ago[API_LANG_SET];
        }

        if ($v == "second") {
            $vv = $names[$v][API_LANG_SET];
            return 1 . $vv . $ago[API_LANG_SET];
        }
    }
}

/**
 * 获取客户端浏览器信息
 * @return string 
 */
function get_broswer() {
    $sys = $_SERVER['HTTP_USER_AGENT'];  //获取用户代理字符串
    if (stripos($sys, "Firefox/") > 0) {
        preg_match("/Firefox\/([^;)]+)+/i", $sys, $b);
        $exp[0] = "Firefox";
        $exp[1] = $b[1];  //获取火狐浏览器的版本号
    } elseif (stripos($sys, "Maxthon") > 0) {
        preg_match("/Maxthon\/([\d\.]+)/", $sys, $aoyou);
        $exp[0] = "Maxthon";
        $exp[1] = $aoyou[1];
    } elseif (stripos($sys, "MSIE") > 0) {
        preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
        $exp[0] = "IE";
        $exp[1] = $ie[1];  //获取IE的版本号
    } elseif (stripos($sys, "OPR") > 0) {
        preg_match("/OPR\/([\d\.]+)/", $sys, $opera);
        $exp[0] = "Opera";
        $exp[1] = $opera[1];
    } elseif (stripos($sys, "Edge") > 0) {
        //win10 Edge浏览器 添加了chrome内核标记 在判断Chrome之前匹配
        preg_match("/Edge\/([\d\.]+)/", $sys, $Edge);
        $exp[0] = "Edge";
        $exp[1] = $Edge[1];
    } elseif (stripos($sys, "Chrome") > 0) {
        preg_match("/Chrome\/([\d\.]+)/", $sys, $google);
        $exp[0] = "Chrome";
        $exp[1] = $google[1];  //获取google chrome的版本号
    } elseif (stripos($sys, 'rv:') > 0 && stripos($sys, 'Gecko') > 0) {
        preg_match("/rv:([\d\.]+)/", $sys, $IE);
        $exp[0] = "IE";
        $exp[1] = $IE[1];
    } else {
        $exp[0] = "";
        $exp[1] = "";
    }
    return $exp[0] . '(' . $exp[1] . ')';
}

/**
 * 获取客户端操作系统信息
 * @return string 
 */
function get_os() {
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $os = false;

    if (preg_match('/win/i', $agent) && strpos($agent, '95')) {
        $os = 'Windows 95';
    } else if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90')) {
        $os = 'Windows ME';
    } else if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent)) {
        $os = 'Windows 98';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent)) {
        $os = 'Windows Vista';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent)) {
        $os = 'Windows 7';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent)) {
        $os = 'Windows 8';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent)) {
        $os = 'Windows 10'; #添加win10判断
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent)) {
        $os = 'Windows XP';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent)) {
        $os = 'Windows 2000';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent)) {
        $os = 'Windows NT';
    } else if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent)) {
        $os = 'Windows 32';
    } else if (preg_match('/linux/i', $agent)) {
        $os = 'Linux';
    } else if (preg_match('/unix/i', $agent)) {
        $os = 'Unix';
    } else if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent)) {
        $os = 'SunOS';
    } else if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent)) {
        $os = 'IBM OS/2';
    } else if (preg_match('/Mac/i', $agent) && preg_match('/PC/i', $agent)) {
        $os = 'Macintosh';
    } else if (preg_match('/PowerPC/i', $agent)) {
        $os = 'PowerPC';
    } else if (preg_match('/AIX/i', $agent)) {
        $os = 'AIX';
    } else if (preg_match('/HPUX/i', $agent)) {
        $os = 'HPUX';
    } else if (preg_match('/NetBSD/i', $agent)) {
        $os = 'NetBSD';
    } else if (preg_match('/BSD/i', $agent)) {
        $os = 'BSD';
    } else if (preg_match('/OSF1/i', $agent)) {
        $os = 'OSF1';
    } else if (preg_match('/IRIX/i', $agent)) {
        $os = 'IRIX';
    } else if (preg_match('/FreeBSD/i', $agent)) {
        $os = 'FreeBSD';
    } else if (preg_match('/teleport/i', $agent)) {
        $os = 'teleport';
    } else if (preg_match('/flashget/i', $agent)) {
        $os = 'flashget';
    } else if (preg_match('/webzip/i', $agent)) {
        $os = 'webzip';
    } else if (preg_match('/offline/i', $agent)) {
        $os = 'offline';
    } else {
        $os = '';
    }
    return $os;
}

/**
 * 判断是否是IOS系统，非IOS系统全部归类为Android
 * @return string
 */
function is_ios() {
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    if (strpos($agent, 'iphone') || strpos($agent, 'ipad')) {
        return true;
    }

    return false;
}

/**
 * 获取图片的绝对路径
 * @param type $imgUrl
 * @return boolean
 */
function getImgOrignal($imgUrl) {
    if (!$imgUrl) {
        return false;
    }
    $filePath = dirname(realpath(APP_PATH)) . DIRECTORY_SEPARATOR . 'Public' . DIRECTORY_SEPARATOR;
    $filePath = str_replace('\\', '/', $filePath);

    $imgs = explode('/', $imgUrl);
    $lastName = array_pop($imgs);

    $lasts = explode('thumb_', $lastName);
    $realLastName = array_pop($lasts);

    $middleDir = join(DIRECTORY_SEPARATOR, $imgs);

    $return = array(
        'file_path' => $filePath,
        'file_save_path' => $filePath . $middleDir,
        'file_src' => $filePath . $middleDir . DIRECTORY_SEPARATOR . $realLastName,
        'file_source' => $imgUrl
    );

    return array_map(function($v) {
        return str_replace('\\', '/', $v);
    }, $return);
}

function flush_buffers() {
    ob_end_flush();
    flush();
    ob_start('ob_callback');
}

function ob_callback($buffer) {
    return $buffer . str_repeat(' ', max(0, 4097 - strlen($buffer)));
}

/**
 * 自定义调试函数
 */
function ls($msg, $isExit = true) {
    header("Content-type:text/html;charset=utf-8");
    echo '<pre  style="color:#9B0067;font-size:13px;">';
    print_r($msg);
    echo '</pre>';
    nl();
    if ($isExit) {
        exit();
    }
    nl();
}

function dls($msg, $isExit = true) {
    header("Content-type:text/html;charset=utf-8");
    echo '<pre style="font-size:13px;color:#9B0067">';
    var_dump($msg);
    echo '</pre>';
    nl();
    if ($isExit) {
        exit();
    }
}

function de($msg, $isExit = true) {
    header("Content-type:text/html;charset=utf-8");
    echo '<pre  style="font-size:13px;color:#473C8B;">';
    var_export($msg);
    echo '</pre>';
    nl();
    if ($isExit) {
        exit();
    }
}

function le($msg, $isExit = true, $isTwoLine = true) {
    header("Content-type:text/html;charset=utf-8");
    echo '<pre  style="color:#008200;font-size:12px;">';
    echo $msg;
    echo '</pre>';
    if ($isTwoLine) {
        nl();
    }
    if ($isExit) {
        exit();
    }
}

function nl($num = 0) {
    $rn = (php_sapi_name() === 'cli') ? "\r\n" : "<br>";
    if ($num) {
        for ($i = 1; $i <= $num; $i++) {
            echo $rn;
        }
    } else {
        echo $rn;
    }
}

function df($time, $isExit = true) {
    header("Content-type:text/html;charset=utf-8");
    echo '<pre  style="color:#00008B;font-size:12px;">';
    if (is_numeric($time)) {
        echo date("Y-m-d H:i:s", $time);
    } else {
        echo strtotime($time);
    }
    echo '</pre>';
    nl();
    $isExit and exit();
}

function jf($jsonstr, $isExit = true) {
    header("Content-type:text/html;charset=utf-8");
    echo '<pre  style="color:#2E8B57;font-size:12px;">';
    print_r(json_decode($jsonstr, true));
    echo '</pre>';
    nl();
    $isExit and exit();
}

function dlg($msg) {
    echo '<script text="text/javascript">';
    $str = json_encode($msg, JSON_UNESCAPED_UNICODE);
    echo 'console.group("%c%s", "color:#C41A16;font-size:14px;", "=== RUNTIME PHP ===");';
//    echo 'console.log("%c%s", "color:green;font-size:12px;", JSON.stringify(\''.$str.'\'));';
    echo 'console.log("%c%o", "color:green;font-size:12px;", ' . $str . ');';
//    echo 'console.log('.$str.');';
    echo 'console.groupEnd();';
    echo '</script>';
    nl();
}

function dli($msg) {
    echo '<script text="text/javascript">';
    $str = json_encode($msg, JSON_UNESCAPED_UNICODE);
    echo 'console.group("%c%s", "color:#C41A16;font-size:14px;", "=== RUNTIME PHP ===");';
//    echo 'console.info("%c%s", "color:green;font-size:12px;", JSON.stringify(\''.$str.'\'));';
    echo 'console.info("%c%o", "color:green;font-size:12px;", ' . $str . ');';
//    echo 'console.log('.$str.');';
    echo 'console.groupEnd();';
    echo '</script>';
    nl();
}

function i_array_column($input, $columnKey, $indexKey = null) {
    if (!function_exists('array_column')) {
        $columnKeyIsNumber = (is_numeric($columnKey)) ? true : false;
        $indexKeyIsNull = (is_null($indexKey)) ? true : false;
        $indexKeyIsNumber = (is_numeric($indexKey)) ? true : false;
        $result = array();
        foreach ((array) $input as $key => $row) {
            if ($columnKeyIsNumber) {
                $tmp = array_slice($row, $columnKey, 1);
                $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : null;
            } else {
                $tmp = isset($row[$columnKey]) ? $row[$columnKey] : null;
            }
            if (!$indexKeyIsNull) {
                if ($indexKeyIsNumber) {
                    $key = array_slice($row, $indexKey, 1);
                    $key = (is_array($key) && !empty($key)) ? current($key) : null;
                    $key = is_null($key) ? 0 : $key;
                } else {
                    $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                }
            }
            $result[$key] = $tmp;
        }
        return $result;
    } else {
        return array_column($input, $columnKey, $indexKey);
    }
}

/**
 * 将数字装换为byte数组
 * @see http://www.hishenyi.com/archives/178
 * @param type $num
 * @return type
 */
function int2byte($num) { //$num 可以传数字
    $num = decbin($num);  //decbin 是php自带的函数，可以把十进制数字转换为二进制

    $num = substr($num, -8); //取后8位
    $sign = substr($num, 0, 1); //截取 第一位 也就是高位，用来判断到底是负的还是正的
    if ($sign == 1) {  //高位是1 代表是负数 ,则要减去256
        return bindec($num) - 256; //bindec 也是php自带的函数，可以把二进制数转为十进制
    } else {
        return bindec($num);
    }
}

/**
 * 将字符串转换为byte数组
 * @param type $string
 * @return type
 */
function string2bytes($string) {
    $bytes = array();
    for ($i = 0; $i < strlen($string); $i++) {    //遍历每一个字符 用ord函数把它们拼接成一个php数组
        $bytes[] = ord($string[$i]);
    }
    return $bytes;
}

/**
 * 将byte数组转化为字符串
 * @param type $bytes
 * @return type
 */
function bytes2string($bytes) {
    $str = '';
    foreach ($bytes as $ch) {
        $str .= chr($ch);    //这里用chr函数
    }
    return $str;
}

/**
 * 格式化app端的版本号
 * @param type $version
 * @return int
 */
function formatAppVersion($version) {
    if (!$version) {
        return 0;
    }

    $version = trim(strtolower($version), 'v');
    $version = str_replace('.', '', $version);
    if (strlen($version) > 3) {
        $version = substr($version, 0, 3);
    }
    return (int) $version;
}

<?php

namespace Components\String;

/**
 * 
 * 注音
 * @usage：
 * 	$objZhuYin = new Components\String\ZhuYin();
 * 	print_r($objZhuYin->conversion('负离子去屑止痒洗发露', true, false));
 *
 */
class ZhuYin {

    private $mywords = array();

    /**
     * @name __construct
     * @desc 构造函数
     */
    public function __construct() {
        $tmp = array();
        $file = file(dirname(__FILE__) . DIRECTORY_SEPARATOR . '/zhuyin_table_ISCCD.txt');
        foreach ($file as $k => $v) {
            $tmp_str = explode(":|", $v);
            $tmp[$tmp_str[0]] = explode("|", trim($tmp_str[1]));
        }
        $this->mywords = $tmp;
    }

    /**
     * @name CToE
     * @desc 中文转英文
     * @param array $list
     */
    public function CToE($list) {
        $return = array();
        if (empty($list))
            return;
        foreach ($list as $k => $v) {
            if (strlen($v) == 1) { //如果是英文
                $return[] = array($v);
            } else { //如果是中文
                if (isset($this->mywords[$v])) {
                    $return[] = $this->mywords[$v];
                }
            }
        }
        return $return;
    }

    /**
     * @name conversion
     * @desc Utf8编码切词
     * @param string $words
     * @param bool $return_complex  是否复合输出,true用数组返回所有可能,false字符串返回一种可能
     * @param bool $return_prefix   是否输出首字母组合,默认为true
     * @param bool $return_english  是否为纯中文输出,默认true
     * @return mixed $results
     * @access public
     */
    public function conversion($words, $return_complex = true, $return_prefix = true, $return_english = true) {
        $words_list = $english_list = $return_list = array();
        $words_list = self::splitWords($words, $return_english);
        $english_list = $this->CToE($words_list);
        if (empty($english_list))
            return array();
        if ($return_complex === true) {
            $compelte_list = $prefix_list = array();
            foreach ($english_list as $k => $v) {
                if (empty($compelte_list)) {
                    $compelte_list = $v;
                    if ($return_prefix === true) {
                        foreach ($v as $key => $value) {
                            $prefix_list[$key] = substr($value, 0, 1);
                        }
                    }
                    continue;
                }
                $num = count($v);
                $tmp = $ptmp = array();
                foreach ($compelte_list as $key => $value) {
                    if ($num == 1) {//只有一个音
                        $tmp[] = $value . $v[0];
                        if ($return_prefix === true) {
                            $ptmp[] = $prefix_list[$key] . substr($v[0], 0, 1);
                        }
                    } else {//多音字处理 
                        for ($i = 0; $i < $num; $i++) {
                            $tmp[] = $value . $v[$i];
                            if ($return_prefix == true) {
                                $ptmp[] = $prefix_list[$key] . substr($v[$i], 0, 1);
                            }
                        }
                        $compelte_list = $tmp;
                    }
                }
                $compelte_list = $tmp;
                $prefix_list = $ptmp;
            }
            $return_list = array_merge($compelte_list, $prefix_list);
        } else {
            $compelte_list = $prefix_list = "";
            foreach ($english_list as $k => $v) {
                $compelte_list .= $v[0];
                if (true === $return_prefix)
                    $prefix_list .= substr($v[0], 0, 1);
            }
            if (true === $return_prefix)
                $return_list = array($compelte_list, $prefix_list);
            else
                $return_list = array($compelte_list);
        }
        return $return_list;
    }

    /**
     * @name splitWords
     * @desc Utf8编码切词
     * @param string $words
     * @param bool $return_english 是否返回英文
     * @return array $results
     * @access public
     */
    public static function splitWords($words, $return_english) {
        $results = array();
        $len = strlen($words);
        if ($len == 0)
            return $results;
        for ($i = 0; $i < $len; $i++) {
            $unicode = ord($words[$i]);
            if ($unicode >= 129) {//如果为汉字
                $results[] = $words[$i] . $words[++$i] . $words[++$i];
                $results[] = ' ';
            }
            if ($return_english === true) {//如果为英文
                if (($unicode >= 65 && $unicode <= 90) || ($unicode >= 97 && $unicode <= 122) || $unicode == 227 || $unicode == 32
                ) {
                    $results[] = $words[$i];
                }
            }
        }
        return $results;
    }

}

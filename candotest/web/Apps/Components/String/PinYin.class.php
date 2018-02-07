<?php

namespace Components\String;

/**
 * 对utf8的汉字进行注音，不考虑多音字，如果存在多音字，使用ZhuYin.class.php
 * 	$objPinYin = new Components\String\PinYin();
 * 	print_r($objPinYin->toPy('不考虑多音字', true));
 *
 */
class PinYin {

    private $u8DataTxt = array();

    /**
     * @name __construct
     * @desc 构造函数
     */
    public function __construct() {
        $tmp = array();
        $file = file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'u8_table_data.txt');
        $fileData = explode('|', $file[0]);
        $pinyins = array();
        foreach ($fileData as $v) {
            $dataArray = explode(':', $v);
            $pinyins[$dataArray[0]] = $dataArray[1];
        }
        $this->u8DataTxt = $pinyins;
    }

    /**
     * @name CToE
     * @desc 中文转英文
     * @param array $list
     */
    public function toPy($str, $isFirst) {
        
        $str = trim($str);
        $len = strlen($str);
        if ($len < 3) {
            return $str;
        }
        
        $rs = array();
        $first = array();
        for ($i = 0; $i < $len; $i++) {
            $o = ord($str[$i]);
            if ($o < 0x80) {
                if (($o >= 48 && $o <= 57) || ($o >= 97 && $o <= 122)) {
                    $rs[] = $str[$i]; // 0-9 a-z
                } elseif ($o >= 65 && $o <= 90) {
                    $rs[] = strtolower($str[$i]); // A-Z
                } else {
                    $rs[] = '_';
                }
            } else {
                $z = $str[$i] . $str[++$i] . $str[++$i];
                if (isset($this->u8DataTxt[$z])) {
                    if ($isFirst) {
                        $first[] = strtoupper($this->u8DataTxt[$z][0]);
                    }
                    $rs[] = $this->u8DataTxt[$z];
                } else {
                    $rs[] = '_';
                }
            }
        }
        
        return $isFirst ? array(
            'first' => $first,
            'pinyin' => $rs
        ) : $rs;
    }

}

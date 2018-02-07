<?php

namespace Components\PHPQrcode;

class PHPQrcode {
    
    /**
     * 引用phpqrcode，使用时需要显式的调用init方法，否则加载不到
     * 调用方式：
     *  1.原生调用：
     *      PHPQrcode::init();
     *      \QRcode::png('text');
     * 
     *  2.本类调用(需要显式的在本类中定义方法，即重新定义一次)：
     *      PHPQrcode::png('text');
     */
    static public function init() {
        require_once 'phpqrcode.php';
    }
    
    
    /**
     * 生成png图片
     * @param type $text            文本内容
     * @param type $outfile         表示是否输出二维码图片文件，默认否；
     * @param type $level           表示容错率，也就是有被覆盖的区域还能识别，分别是 L（QR_ECLEVEL_L，7%），M（QR_ECLEVEL_M，15%），Q（QR_ECLEVEL_Q，25%），H（QR_ECLEVEL_H，30%）
     * @param type $size            二维码图大小，1－10可选，数字越大图片尺寸越大
     * @param type $margin          表示二维码周围边框空白区域间距值
     * @param type $saveandprint    表示是否保存二维码并显示
     * @return type
     */
    static public function png($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4, $saveandprint=false) {
        self::init();
        return \QRcode::png($text, $outfile, $level, $size, $margin, $saveandprint);
    }
    
}

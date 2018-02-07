<?php

/**
 * Emoji class for php 
 * 
 * 提供APP端Emoji表情转码操作
 * 
 * Author: nanco (nanco_pu@163.com)
 */

namespace Components\Emoji;

class Emoji {
    
    static public function init() {
        require_once 'emoji.php';
    }
    
    
    /**
     * 解析 emoji 表情 => bbcode
     * 从表情码转UTF8编码 如： \xe2\x9c\x85 => [:2705]
     * @param string $message
     * @return string
     */
    static public function emoji_unified_to_bbcode($message) {
        self::init();
        return emoji_unified_to_bbcode($message);
    }
    
    /**
     * 解析 bbcode => emoji 表情
     * 从UTF8编码转表情码 如： [:2705] => \xe2\x9c\x85
     * @param string $message
     * @return string
     */
    static public function emoji_bbcode_to_unified($message) {
        self::init();
        return emoji_bbcode_to_unified($message);
    }
    
    /**
     * 解析 emoji 表情 => html
     * 从表情码转html
     * @param string $message
     * @return string
     */
    static public function emoji_unified_to_html($message) {
        self::init();
        return emoji_unified_to_html($message);
    }
    
    
    /**
     * 解析自定义表情
     * @param string $message
     * @return string
     */
    static public function parseCustomFace($message) {
        self::init();
        return parseCustomFace($message);
    }
    
    
}
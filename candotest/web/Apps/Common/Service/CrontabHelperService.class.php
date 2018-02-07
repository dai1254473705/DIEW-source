<?php

/**
 * 定时任务外部辅助类
 * @Todo 
 *  1.用于判断定时任务是否已经运行
 *  2.在系统中需要独立运行定时任务时，进行强制等待操作
 */

namespace Common\Service;

use Think\Model;

class CrontabHelperService extends Model {
    
    static private $filePath;
    
    /**
     * 解析cli中的控制器和方法，返回对应的数据
     * @param type $crontabName
     */
    static public function _parse($crontabName) {
        $result = array();
        if (!$crontabName) {
            return $result;
        }
        
        # 初始化filepath
        $filePath = realpath(APP_PATH);
        self::$filePath = self::formatDirectorySeparator($filePath . DIRECTORY_SEPARATOR . 'Cli/Run/Data/Pid');
        if (!is_dir(self::$filePath)) {
            @mkdir(self::$filePath, '0755', true);
        }
        
        # 建立唯一值文件，并返回
        $filename = md5($crontabName);
        $realFile = self::formatDirectorySeparator(self::$filePath . DIRECTORY_SEPARATOR . $filename);
        
        return array(
            'path' => self::$filePath,
            'file' => $realFile,
            'content' => $filename . "||" . $crontabName
        );
        
    }
    
    /**
     * 判断传入的脚本是否在运行
     * @return boolean
     */
    static public function isRunning($crontabName) {
        $run = self::_parse($crontabName);
        if (empty($run)) {
            return false;
        }
        
        if (isset($run['file']) && file_exists($run['file'])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 创建唯一值
     * @return type
     */
    static public function generate($crontabName){
        $run = self::_parse($crontabName);
        if (empty($run)) {
            return false;
        }
        
        $file = $run['file'];
        if (!is_writable($file)) {
            @chmod($file, '0755');
        }
        
        $size = @file_put_contents($file, $run['content']);
        if (!$size) {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * 清除唯一值
     * @param type $crontabName
     */
    static public function clean($crontabName) {
        $run = self::_parse($crontabName);
        if (empty($run)) {
            return false;
        }
        
        $file = $run['file'];
        if (!is_writable($file)) {
            @chmod($file, '0755');
        }
        
        @unlink($file);
        
        if (file_exists($file)) {
            return false;
        }
        
        return true;
    }

    
    
    
    /**
     * 格式化file的路径分隔符
     * @param type $file
     * @return type
     */
    static public function formatDirectorySeparator($file) {
        $file = str_replace("/", DIRECTORY_SEPARATOR, $file);
        $file = str_replace("\\", DIRECTORY_SEPARATOR, $file);
        return $file;
    }
    
}

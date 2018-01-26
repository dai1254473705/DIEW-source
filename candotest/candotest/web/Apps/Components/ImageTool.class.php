<?php

/**
 * 图片上传处理工具类
 * 功能1、上传图片；
 * 功能2、重新生成图片，给图片加水印等
 * 使用方法：
 * 
 * $format 格式化后的$_FILES数组
 * [0] => Array
        (
            [name] => 0020080141.jpg
            [type] => image/jpeg
            [tmp_name] => E:\wamp\tmp\php4F26.tmp
            [error] => 0
            [size] => 52328
        )
    [1] => Array
        (
            [name] => 5396976_130221219357_2.jpg
            [type] => image/jpeg
            [tmp_name] => E:\wamp\tmp\php4F27.tmp
            [error] => 0
            [size] => 120425
        )
 * 
 * $is_thumb 是否压缩 true or false
 * 
 * $imageToolObj = new \Components\ImageTool();
 * $uploadImgs = $imageToolObj->upload($format, $is_thumb);
 * 成功返回：$uploadImgs
 * 获取错误：$imageToolObj->getError();
 */

namespace Components;

use Think\Upload;
use Think\Image;

class ImageTool {

    private $uploadObj;
    private $imageObj;
    private $error;
    private $thumbWidth = 720;
    private $thumbHeight = 600;

    /**
     * 根据项目定义上传config
     * @var type 
     */
    private $config = array(
        'maxSize' => 0, //上传的文件大小限制 (0-不做限制)
        'exts' => array('jpg', 'png', 'gif', 'jpeg','csv'), //允许上传的文件后缀
        'autoSub' => true, //自动子目录保存文件
        'subName' => array('date', 'Y/m/d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt' => '', //文件保存后缀，空则使用原后缀
        'replace' => false, //存在同名是否覆盖
        'hash' => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调，如果存在返回文件信息数组
        'driver' => '', // 文件上传驱动
        'driverConfig' => array(), // 上传驱动配置
    );

    /**
     * @brief get php.ini minimum post_max_size and upload_max_filesize
     */
    public static function getMaxSize() {
        return min(ini_get('upload_max_filesize'), ini_get('post_max_size'));
    }

    public function __construct($param) {

        // 默认配置
        $this->initConfig($param);
        $this->uploadObj = new Upload($this->config);
        $this->imageObj = new Image();
    }

    /**
     * 配置config的默认值
     */
    private function initConfig($outConfig) {

        if (!is_array($outConfig)) {
            $outConfig = array();
        }

        //为了统一性，外部使用rootpath时需要定义相对路径，在此处进行绝对路径的转换
        $outRootPath = '';
        if (isset($outConfig['rootPath'])) {
            $outRootPath = DIRECTORY_SEPARATOR . $outConfig['rootPath'];
            unset($outConfig['rootPath']);
        }

        //计算保存根路径
        $realpath = realpath(APP_PATH);
        $rootPathArr = explode(DIRECTORY_SEPARATOR, $realpath);
        array_pop($rootPathArr);
        $rootPath = join(DIRECTORY_SEPARATOR, $rootPathArr);
        $initConfig = array(
            'rootPath' => $rootPath . DIRECTORY_SEPARATOR . "Public" . $outRootPath,
            'savePath' => DIRECTORY_SEPARATOR . "Uploads" . DIRECTORY_SEPARATOR
        );

        $this->config = array_merge($this->config, $initConfig, $outConfig);
    }

    /**
     * 获取错误信息
     * @return type
     */
    public function getError() {
        return $this->error;
    }

    /**
     * 默认批量上传图片
     * @param type $fileData    原图地址
     * @param type $is_thumb    是否生成缩略图
     * @param type $width       缩略图宽
     * @param type $height      缩略图高
     * @return boolean
     */
    public function upload($fileData, $is_thumb = false, $width = null, $height = null) {
        $return = $this->uploadObj->upload($fileData);
        
        if (!$return) {
            $this->error = $this->uploadObj->getError();
            return false;
        }
        // 规范图片分隔符显示
        $imgData = array();
        foreach ($return as $key => $value) {
            $value['url'] = str_replace(DIRECTORY_SEPARATOR, "/", $value['savepath']) . $value['savename'];
            $imgData[$key] = $value;
        }

        //生成缩略图
        if ($is_thumb) {
            $thumbW = $width ? $width : $this->thumbWidth;
            $thumbH = $height ? $height : $this->thumbHeight;
            $realPath = dirname(realpath(APP_PATH));
            foreach ($imgData as $key => $one) {
                $imgPath = $realPath . DIRECTORY_SEPARATOR . "Public" . DIRECTORY_SEPARATOR . ltrim($one['url'], '/');
                $imgData[$key]['thumb'] = $this->thumb($imgPath, $thumbW, $thumbH);
            }
        }


        return $imgData;
    }
    
    /**
     * 批量上传图片（自定义缩略图宽高）
     * @param type $fileData
     * @param type $is_thumb 缩略图
     * @return type
     */
    public function uploadThumb($fileData, $thumbWidth, $thumbHeight) {
        $return = $this->uploadObj->upload($fileData);
        if (!$return) {
            $this->error = $this->uploadObj->getError();
            return false;
        }
        // 规范图片分隔符显示
        $imgData = array();
        foreach ($return as $key => $value) {
            $value['url'] = str_replace(DIRECTORY_SEPARATOR, "/", $value['savepath']) . $value['savename'];
            $imgData[$key] = $value;
        }
        
        //生成缩略图
        $realPath = dirname(realpath(APP_PATH));
        foreach ($imgData as $key => $one) {
            $imgPath = $realPath . DIRECTORY_SEPARATOR . "Public" . DIRECTORY_SEPARATOR . ltrim($one['url'], '/');
            $w = $thumbWidth ? $thumbWidth : $this->thumbWidth;
            $h = $thumbHeight ? $thumbHeight : $this->thumbHeight;
            $imgData[$key]['thumb'] = $this->thumb($imgPath, $w, $h);
        }
        
        return $imgData;
    }
    
    
    /**
     * 批量上传图片（定高生成缩略图）
     * @param type $fileData
     * @param type $thumbHeight 缩略图高度
     * @return type
     */
    public function uploadThumbByHeight($fileData, $thumbHeight) {
        $return = $this->uploadObj->upload($fileData);
        if (!$return) {
            $this->error = $this->uploadObj->getError();
            return false;
        }
        // 规范图片分隔符显示
        $imgData = array();
        foreach ($return as $key => $value) {
            $value['url'] = str_replace(DIRECTORY_SEPARATOR, "/", $value['savepath']) . $value['savename'];
            $imgData[$key] = $value;
        }
        
        //生成缩略图
        $realPath = dirname(realpath(APP_PATH));
        foreach ($imgData as $key => $one) {
            $imgPath = $realPath . DIRECTORY_SEPARATOR . "Public" . DIRECTORY_SEPARATOR . ltrim($one['url'], '/');
            $h = $thumbHeight ? $thumbHeight : $this->thumbHeight;
            $imgData[$key]['thumb'] = $this->thumbByHeight($imgPath, $h);
        }
        
        return $imgData;
    }
    
    
    /**
     * 批量上传图片（定宽生成缩略图）
     * @param type $fileData
     * @param type $thumbWidth 缩略图宽度
     * @return type
     */
    public function uploadThumbByWidth($fileData, $thumbWidth) {
        $return = $this->uploadObj->upload($fileData);
        if (!$return) {
            $this->error = $this->uploadObj->getError();
            return false;
        }
        // 规范图片分隔符显示
        $imgData = array();
        foreach ($return as $key => $value) {
            $value['url'] = str_replace(DIRECTORY_SEPARATOR, "/", $value['savepath']) . $value['savename'];
            $imgData[$key] = $value;
        }
        
        //生成缩略图
        $realPath = dirname(realpath(APP_PATH));
        foreach ($imgData as $key => $one) {
            $imgPath = $realPath . DIRECTORY_SEPARATOR . "Public" . DIRECTORY_SEPARATOR . ltrim($one['url'], '/');
            $w = $thumbWidth ? $thumbWidth : $this->thumbWidth;
            $imgData[$key]['thumb'] = $this->thumbByWidth($imgPath, $w);
        }
        
        return $imgData;
    }
    
    

    /**
     * 生成图片的缩略图
     * @param type $imgPath		原图片路径
     * @param type $width		缩略图的宽度	
     * @param type $height		缩略图的高度
     * @param type $savePath	缩略图保存的路径，默认为空，保存到原图片路径下
     * @param type $saveName	缩略图保存名称，默认为空，则在原图的名称上加上前缀“thumb_”
     */
    public function thumb($imgPath, $width, $height, $savePath = null, $saveName = null) {
        $return = array(
            'status' => false,
            'msg' => ''
        );
        if (!$imgPath) {
            return $return['msg'] = "原图路径不能为空";
        }
        if (!$width || !$height) {
            return $return['msg'] = "请指定缩略图的宽或者高";
        }
        //计算缩略图保存路径
        $thumbSavePath = '';
        $thumbSaveName = '';
        if ($savePath) {
            $thumbSavePath = $savePath;
        } else {
            $thumbSavePath = dirname($imgPath);
            $thumbSaveName = "thumb_" . trim(str_replace($thumbSavePath, '', $imgPath), '/');
        }
        if ($saveName) {
            $thumbSaveName = $saveName;
        }
        $realThumbSavePath = $thumbSavePath . DIRECTORY_SEPARATOR . $thumbSaveName;
        $realThumbSavePath = str_replace(DIRECTORY_SEPARATOR, '/', $realThumbSavePath);

        //开始缩略图
        $this->imageObj = new Image();
        $this->imageObj->open($imgPath);
        $this->imageObj->thumb($width, $height);
        $this->imageObj->save($realThumbSavePath);
        
        if (!file_exists($realThumbSavePath)) {
            return $return['msg'] = "操作失败";
        }
        //操作成功，返回缩略图的url
        $replace = str_replace(DIRECTORY_SEPARATOR, '/', dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR . "Public");
        $thumbUrl = str_replace($replace, '', $realThumbSavePath);
        return $return = array(
            'status' => true,
            'msg' => $thumbUrl
        );
    }
    
    
    
    /**
     * 定高（宽度比例根据高度计算） 生成图片的缩略图
     * @param type $imgPath		原图片路径	
     * @param type $height		缩略图的高度
     * @param type $savePath	缩略图保存的路径，默认为空，保存到原图片路径下
     * @param type $saveName	缩略图保存名称，默认为空，则在原图的名称上加上前缀“thumb_”
     */
    public function thumbByHeight($imgPath, $height, $savePath = null, $saveName = null) {
        $return = array(
            'status' => false,
            'msg' => ''
        );
        if (!$imgPath) {
            return $return['msg'] = "原图路径不能为空";
        }
        if (!$height) {
            return $return['msg'] = "请指定缩略图的高";
        }
        
        //计算缩略图保存路径
        $thumbSavePath = '';
        $thumbSaveName = '';
        if ($savePath) {
            $thumbSavePath = $savePath;
        } else {
            $thumbSavePath = dirname($imgPath);
            $thumbSaveName = "thumb_" . trim(str_replace($thumbSavePath, '', $imgPath), '/');
        }
        if ($saveName) {
            $thumbSaveName = $saveName;
        }
        $realThumbSavePath = $thumbSavePath . DIRECTORY_SEPARATOR . $thumbSaveName;
        $realThumbSavePath = str_replace(DIRECTORY_SEPARATOR, '/', $realThumbSavePath);

        //开始缩略图
        $this->imageObj = new Image();
        $this->imageObj->open($imgPath);
        
        # 原图尺寸
        $oriW = $this->imageObj->width();
        $oriH = $this->imageObj->height();
        
        //原图不论高度是否小于缩略图高度，都进行缩略, 计算缩放比例
        $scale = ($oriH > $height) ? $height/$oriH : $oriH/$height;

        //设置缩略图的坐标及宽度和高度
        $x = $y = 0;
        $width  = $oriW * $scale;
        
        $this->imageObj->crop($oriW, $oriH, $x, $y, $width, $height);
        $this->imageObj->save($realThumbSavePath);
        
        if (!file_exists($realThumbSavePath)) {
            return $return['msg'] = "操作失败";
        }
        //操作成功，返回缩略图的url
        $replace = str_replace(DIRECTORY_SEPARATOR, '/', dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR . "Public");
        $thumbUrl = str_replace($replace, '', $realThumbSavePath);
        return $return = array(
            'status' => true,
            'msg' => $thumbUrl
        );
    }
    
    
    /**
     * 【非强制缩放，当原图小于缩放的宽度时，保持原图】定宽（高度比例根据宽度计算） 生成图片的缩略图
     * @param type $imgPath		原图片路径	
     * @param type $width		缩略图的宽度
     * @param type $savePath	缩略图保存的路径，默认为空，保存到原图片路径下
     * @param type $saveName	缩略图保存名称，默认为空，则在原图的名称上加上前缀“thumb_”
     */
    public function thumbByWidth($imgPath, $width, $savePath = null, $saveName = null) {
        $return = array(
            'status' => false,
            'msg' => ''
        );
        if (!$imgPath) {
            return $return['msg'] = "原图路径不能为空";
        }
        if (!$width) {
            return $return['msg'] = "请指定缩略图的宽";
        }
        
        //计算缩略图保存路径
        $thumbSavePath = '';
        $thumbSaveName = '';
        if ($savePath) {
            $thumbSavePath = $savePath;
        } else {
            $thumbSavePath = dirname($imgPath);
            $thumbSaveName = "thumb_" . trim(str_replace($thumbSavePath, '', $imgPath), '/');
        }
        if ($saveName) {
            $thumbSaveName = $saveName;
        }
        $realThumbSavePath = $thumbSavePath . DIRECTORY_SEPARATOR . $thumbSaveName;
        $realThumbSavePath = str_replace(DIRECTORY_SEPARATOR, '/', $realThumbSavePath);

        //开始缩略图
        $this->imageObj = new Image();
        $this->imageObj->open($imgPath);
        
        # 原图尺寸
        $oriW = $this->imageObj->width();
        $oriH = $this->imageObj->height();
        
        //原图不论高度是否小于缩略图高度，都进行缩略, 计算缩放比例
        if ($oriW > $width) {
            $scale = $width/$oriW;
        }else{
            $scale = 1;
            $width = $oriW;
        }
//        $scale = ($oriW > $width) ? $width/$oriW : $oriW/$width;

        //设置缩略图的坐标及宽度和高度
        $x = $y = 0;
        $height  = $oriH * $scale;
        
        $this->imageObj->crop($oriW, $oriH, $x, $y, $width, $height);
        $this->imageObj->save($realThumbSavePath);
        
        if (!file_exists($realThumbSavePath)) {
            return $return['msg'] = "操作失败";
        }
        //操作成功，返回缩略图的url
        $replace = str_replace(DIRECTORY_SEPARATOR, '/', dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR . "Public");
        $thumbUrl = str_replace($replace, '', $realThumbSavePath);
        return $return = array(
            'status' => true,
            'msg' => $thumbUrl
        );
    }

}

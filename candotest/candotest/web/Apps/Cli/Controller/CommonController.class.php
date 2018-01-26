<?php

// +----------------------------------------------------------------------
// | cli模式的脚本操作
// +----------------------------------------------------------------------
// | Author: nanco_plg@163.com
// +----------------------------------------------------------------------
//----------------------------------
// cli基础继承类
// 普通触发格式：php /e/wamp/www/project/trunk/cli.php index/index id=1 name=中文
// 严格模式下触发格式：php /e/wamp/www/project/trunk/cli.php index/index id=1 name=中文 do=1
// 开启唯一性且手动终止脚本执行，运行：php /e/wamp/www/project/trunk/cli.php index/index deu=1 删除实例唯一值
// 
// 注意：
// 1.IS_STRICT_MODE 是否严格模式（严格模式下，使用do=1执行脚本）即需要加上参数 do=1
// 2.IS_UNIQUE_ON   是否开启唯一性限制（保证每次只运行一个脚本实例），开启此模式后，请不要删除 Runtime/Temp 目录（使用了文件缓存，文件缓存保存在 Runtime/Temp 下）
// 3.开启唯一性限制后，如果手动结束脚本运行，需要 手动添加参数 deu=1 删除脚本实例唯一值之后，再次运行脚本
//----------------------------------

namespace Cli\Controller;

use Think\Controller;
use Common\Service\CrontabHelperService;

class CommonController extends \Think\Controller {

    const IS_STRICT_MODE = true;    # 是否严格模式（严格模式下，使用do=1执行脚本）
    const IS_UNIQUE_ON = true;      # 是否开启唯一性限制（保证每次只运行一个脚本实例），开启此模式后，请不要删除 Runtime/Temp 目录

    static $unique = true;
    protected $pa = array();        # 存放获取参数
    public $stime = 0;           # 脚本开始运行时间

    /**
     * 构造方法，用于处理环境和参数
     */
    
    public function _initialize($is_unique_on = null) {

        # 识别运行时状态
        $params = $this->discernRuntimeMode();
        $this->pa = $params;

        # 设置运行时间和运行时的最大内存，脚本开始执行时间
        ini_set("max_execution_time", 0);
        ini_set("memory_limit", "2048M");
        header("Content-type:text/html;charset=utf-8");
        $this->stime = time();
        
        # init time
        print "Start Time: " . date('Y-m-d H:i:s', time());
        self::newLine();
        
        # 是否严格模式
        if (self::IS_STRICT_MODE) {
            if (!isset($this->pa['do']) || !$this->pa['do']) {
                self::output("Please add the 'do=1' parameter, and then execute the script");
                exit;
            }
        }

        # 开启唯一性检测
        $runtimeIsUniqueOn = !is_null($is_unique_on) ? $is_unique_on : self::IS_UNIQUE_ON;
        if ($runtimeIsUniqueOn) {
            
            //判断runtime mode
            $crontabName = $this->pa['path'] .'/'. $this->pa['path_info'];
            if (!self::isRunCommandLine()) {
                $crontabName = trim($_SERVER['SCRIPT_NAME'], '/') . '/' . CONTROLLER_NAME . '/' . ACTION_NAME;
            }
            $crontabName = strtolower($crontabName);
            self::$unique = $crontabName;
            $cacheUnique = CrontabHelperService::isRunning(self::$unique);
            if ($cacheUnique) {

                # 检测是否存在删除实例参数
                if ($this->pa['deu']) {
                    CrontabHelperService::clean(self::$unique);
                    echo "The instance has been deleted, please remove the 'deu=1' parameter and run again.";
                    self::newLine();
                    exit;
                }
                print "An instance already exists, please wait for a moment. \nIf the instance is run manually, add the 'deu=1' parameter to delete the instance identifier and run again.";
                self::newLine();
                exit;
            }
            
            CrontabHelperService::generate(self::$unique);
        }
    }

    /**
     * 识别运行时模式
     * @return type
     */
    protected function discernRuntimeMode() {
        $params = array();

        # 验证是否cli执行
        if (IS_CLI || IS_CGI) {

            # cli模式下，获取参数，限定参数传递格式
            $ags = $_SERVER['argv'];
            $params = array(
                'path' => array_shift($ags),
                'path_info' => array_shift($ags),
            );
            foreach ($ags as $key => $value) {
                if (stripos($value, "=")) {
                    list($vk, $vv) = explode('=', $value);
                    $params[$vk] = $vv;
                }
            }
            return $params;
        }

        # 网页端执行时返回参数信息
        list($get, $post) = array(I('get.'), I('post.'));
        $params = array_merge($post, $get);
        echo '<script>function scrollWindow(h){window.scrollTo(h,h);}</script>';
        return $params;
    }

    /**
     * 删除唯一信息，每次脚本成功执行后，需要手动调用删除，IS_UNIQUE_ON开启时有效
     * 不使用__destruct，因为每次脚本执行都会调用，此唯一值适用于脚本成功执行之后操作
     */
    static public function delUnique() {
        if (self::IS_UNIQUE_ON) {
            if (self::$unique) {
                CrontabHelperService::clean(self::$unique);
            }
        }
        
        if (!IS_CLI && !IS_CGI) {
            echo '<script>var h = document.documentElement.scrollHeight || document.body.scrollHeight;scrollWindow(h);</script>';
            self::flush_buffers();
        }
    }

    /**
     * new line
     */
    static public function newLine($num = 1) {
        $rn = (php_sapi_name() === 'cli') ? "\r\n" : "<br>";
        if ($num) {
            for ($i = 1; $i <= $num; $i++) {
                echo $rn;
            }
        } else {
            echo $rn;
        }
        
        if (!IS_CLI && !IS_CGI) {
            echo '<script>var h = document.documentElement.scrollHeight || document.body.scrollHeight;scrollWindow(h);</script>';
            self::flush_buffers();
        }
    }

    /**
     * 输出
     * @param type $msg
     */
    static public function output($msg) {
        self::newLine();
        print "[" . date('Y-m-d H:i:s', time()) . "] " . $msg;
        self::newLine();
        if (!IS_CLI && !IS_CGI) {
            echo '<script>var h = document.documentElement.scrollHeight || document.body.scrollHeight;scrollWindow(h);</script>';
            self::flush_buffers();
        }
    }
    
    /**
     * 终止输出
     * @param type $msg
     */
    static public function _half($msg) {
        self::newLine();
        print "[" . date('Y-m-d H:i:s', time()) . "] Error：" . $msg . " _half";
        if (!IS_CLI && !IS_CGI) {
            self::flush_buffers();
        }
        exit;
    }
    
    /**
     * 设定sleep时间，动态倒计时
     * @param type $seconds
     */
    static public function setSleep($seconds) {
        $i = $seconds;
        $rn = (php_sapi_name() === 'cli') ? "\r\n" : "<br>";
        for($i; $i>=0; $i--){
            self::flush_buffers();
            print $i . '...' . $rn;
            if (!IS_CLI && !IS_CGI) {
                echo '<script>var h = document.documentElement.scrollHeight || document.body.scrollHeight;scrollWindow(h);</script>';
                self::flush_buffers();
            }
            self::flush_buffers();
            sleep(1);
        }
    }
    
    
    /**
     * 设定sleep时间，动态倒计时
     * @param type $seconds
     */
    static public function setSleepProgress($seconds, $suf = null) {
        $i = $seconds;
        for($i; $i>=0; $i--){
            $msg = $suf ? ($i . $suf) : $i;
            if (!IS_CGI && !IS_CLI) {
                self::output($msg);
                self::flush_buffers();
            }else{
                self::output($msg . ' ...<-|');
            }
            sleep(1);
        }
    }
    
    
    /**
     * exit_done 输入结束
     * @param type $msg
     */
    public function exit_done($msg = null) {
        $msg = $msg ? $msg : 'done...';
        self::output($msg);
        $endTime = time() - $this->stime;
        self::output("##############TAKE TIME：{$endTime} Seconds，" . floor($endTime / 60) . " Minutes，" . floor($endTime / 3600) . " Hours##############");
        self::newline();
        self::delUnique();
        
        exit;
    }

    /**
     * 程序执行网页效果实时显示
     */
    static function flush_buffers() {
        ob_end_flush();
        flush();
        ob_start('self::ob_callback');
    }
    
    static function ob_callback($buffer) {
        return $buffer . str_repeat(' ', max(0, 4097 - strlen($buffer)));
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
    
    
    
    /**
     * 是否在命令行模式下运行
     * @return boolean
     */
    static public function isRunCommandLine() {
        if (IS_CLI || IS_CGI) {
            return true;
        }
        return false;
    }

}

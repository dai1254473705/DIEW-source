<?php
namespace Components\Mail;

use Components\Mail\PHPMailer;

/**
 * 
 * 邮件发送类。封装Components\Mail\PHPMailer
 *
 */
class Mail
{
    protected $objPHPMailer;
    
    /**
     * 
     * 用于发smtp邮件的配置信息
     * @var array
     */
    protected $config;

    public function __construct(array $options = null)
    {
    	$this->config = C('PHPMAIL_SMTP');
        $this->objPHPMailer = new PHPMailer();

        $this->objPHPMailer->IsSMTP();
        
        # 设置ssl发送
        // $this->objPHPMailer->SMTPSecure = 'ssl';
        
        $this->objPHPMailer->SMTPAuth = true;

        if(isset($options['HOSTNAME'])){
			$this->objPHPMailer->Host = $options['HOSTNAME'];
        }else{
			$this->objPHPMailer->Host = $this->config['hostname'];
        }

        if(isset($options['PORT'])){
        	$this->objPHPMailer->Port = $options['PORT'];
        }else{
        	$this->objPHPMailer->Port = $this->config['port'];
        }

        if(isset($options['TIMEOUT'])){
			$this->objPHPMailer->Timeout = $options['TIMEOUT'];
        }else{
        	$this->objPHPMailer->Timeout = $this->config['timeout'];
        }

        if(isset($options['USERNAME'])){
        	$this->objPHPMailer->Username = $options['USERNAME'];
        }else{
        	$this->objPHPMailer->Username = $this->config['username'];
        }

        if(isset($options['PASSWORD'])){
        	$this->objPHPMailer->Password = $options['PASSWORD'];
        }else{
        	$this->objPHPMailer->Password = $this->config['password'];
        }

        if(isset($options['CHARSET'])){
        	$this->objPHPMailer->CharSet = $options['CHARSET'];
        }else{
        	$this->objPHPMailer->CharSet = $this->config['charset'];
        }
        
        if(isset($options['FROM'])){
        	$this->objPHPMailer->From = $options['FROM'];
        }else{
        	$this->objPHPMailer->From = $this->config['from'];
        }
        
        if(isset($options['FROMNAME'])){
        	$this->objPHPMailer->FromName = $options['FROMNAME'];
        }else{
        	$this->objPHPMailer->FromName = $this->config['fromname'];
        }

        if(isset($options['Encoding'])){
        	$this->objPHPMailer->Encoding = $options['Encoding'];
        }else{
        	$this->objPHPMailer->Encoding = "base64";
        }

    }

    /**
     *
     * 发送邮件
     * @param array $info 格式：array(
     * "from"     =>  "",//发件人邮箱	默认为(config中 PHPMAIL_SMTP 的配置值)
     * "fromname"   =>  "",//发件人 默认为(config中 PHPMAIL_SMTP 的配置值)
     * "address"    =>  array(           //必填 收件人
     *            array(
     *              "mail"  =>  "",//必填 收件人邮箱
     *              "name"  =>  "",//收件人姓名
     *            )
     *            ……
     *          ),
     * "ccaddress"    =>  array(          //抄送
     *            array(
     *              "mail"  =>  "",//必填 收件人邮箱
     *              "name"  =>  "",//收件人姓名
     *            )
     *            ……
     *          ),
     * "bccaddress"   =>  array(          //密送
     *            array(
     *              "mail"  =>  "",//必填 收件人邮箱
     *              "name"  =>  "",//收件人姓名
     *            )
     *            ……
     *          ),
     * "attachment" =>  array(
     *            "",//附件1 服务器文件全路径 /var/tmp/file.tar.gz
     *            "",//附件2
     *          ),
     * "ishtml"     =>  true|false,//yes or no, send as HTML
     * "subject"      =>  "",//必填 邮件主题
     * "body"       =>  "",//当为空时使用subject的值
     * );
     * @param int $retry_times 发送失败时的重试次数，默认为0，即不重试
     * @param int $retry_frequency 重试频率，单位：秒。默认为1秒
     * @return bool
	 * 
	 * 发送demo 使用方法：
	 * 1.需要在config中配置如下配置：
	 * 'PHPMAIL_SMTP' => array(
			'host' => 'smtp.163.com', //邮件服务器host
			'port' => 25,	//邮件服务器端口
			'username' => 'xxxx', //登录需要发送邮件的邮件服务器的用户名
			'from' => 'qianxunpl726@163.com',	//发件人邮箱
			'fromname' => '邮件测试',	//发件人昵称
			'password' => '',	//登录需要发送邮件的邮件服务器的密码
			'charset' => 'utf8',	//字符
			'timeout' => 20,	//超时时间，默认秒
		),
	 * 2.将此处的代码复制到文件中，
	 * 3.在页面头部引入 use Components\Mail\Mail as Mail;  
	 * 刷新页面时，则可以发送邮件
	 * 
	 * $attachment = array(
			dirname(realpath(APP_PATH)) . DIRECTORY_SEPARATOR . "Public/assets/home/images/img22.png"
		);
		
		$body = <<< EOF
		<html>
			<body>
				<center>
					<a target="_blank" href="http://115.28.7.89/only2/index.php/Home">only2only，您的专属服装定制</a>
					<br>
					<img src="http://115.28.7.89/only2/Public/assets/home/images/img22.png">
				</center>
			</body>
		</html>
EOF;
		
		$email = "924816815@qq.com";
		$info = array(
			"address" => array(//必填 收件人
				array(
					"mail" => $email, //必填 收件人邮箱
				),
//				array(
//					"mail" => "924816815@qq.com", //必填 收件人邮箱
//					"name" => "林"
//				)
			),
			"attachment" => join(',', $attachment),
			"ishtml" => false, //yes or no, send as HTML
			"subject" => "必填 邮件主题", //必填 邮件主题
			"body" => $body, //当为空时使用subject的值
		);
		
		$objMail = new Mail();
		$mailStatus = $objMail->send($info);
		dls($mailStatus,0);
     */
    public function send($info, $retry_times = 0, $retry_frequency = 1)
    {
    	$from = isset($info['from']) ? $info['from'] : '';
        if($from){
			if(!filter_var($from, FILTER_VALIDATE_EMAIL)){
    			return '邮箱格式不正确';
    			exit;
    		}
    		$this->objPHPMailer->From = $from;
    	}
        
    	$fromname = isset($info['fromname']) ? $info['fromname'] : '';
        if($fromname){
    		$this->objPHPMailer->FromName = $fromname;
    	}

    	if(!$info["address"]){
    		return '收件人邮箱不能为空';
    		exit;
    	}
    	if (is_array($info["address"])){
	    	foreach($info["address"] as $address){
	    		if(!$address['mail']){
	    			return '收件人邮箱不能为空';
	    			exit;
	    		}
	            $this->objPHPMailer->AddAddress($address["mail"], $address["name"]);
	        }
    	}else{
    		$address = explode(',', $info["address"]);
    		foreach($address as $add){
    			if(!$add) continue;
	            $this->objPHPMailer->AddAddress(trim($add));
	        }
    	}

    	if (is_array($info["ccaddress"])){
	    	foreach($info["ccaddress"] as $address){
	    		if(!$address["mail"]){
	    			return '抄送收件人邮箱不能为空';
	    			exit;
	    		}
	    		$this->objPHPMailer->AddCC($address["mail"], $address["name"]);
	        }
    	}else{
    		$address = explode(',', $info["ccaddress"]);
    		foreach($address as $add){
    			if(!$add) continue;
	            $this->objPHPMailer->AddCC(trim($add));
	        }
    	}

    	if (is_array($info["bccaddress"])){
    		foreach($info["bccaddress"] as $address){
	    		if(!$address["mail"]){
	    			return '密送收件人邮箱不能为空';
	    			exit;
	    		}
	    		$this->objPHPMailer->AddBCC($address["mail"], $address["name"]);
	        }
    	}else{
    		$address = explode(',', $info["bccaddress"]);
    		foreach($address as $add){
    			if(!$add) continue;
	            $this->objPHPMailer->AddBCC(trim($add));
    		}
    	}

    	$attachment = isset($info["attachment"])?$info["attachment"]:'';
        if($attachment)
        {
        	$attachmenta = explode(',', $attachment);
            foreach($attachmenta as $tmpV)
            {
            	if(!$tmpV) continue;
                $this->objPHPMailer->AddAttachment($tmpV);
            }
        }

        $ishtml = isset($info["ishtml"]) ? $info["ishtml"] : '';
        if($ishtml){
			$this->objPHPMailer->IsHTML($ishtml);
        }else{
        	$this->objPHPMailer->IsHTML(true);
        }

        if(!$info["subject"]){
        	return '主题不能为空！';
        	exit;
        }
        $this->objPHPMailer->Subject = $info["subject"];

        $body = isset($info["body"]) ? $info["body"] : '';
        if(!$body){
        	 $this->objPHPMailer->Body = $info["subject"];
        }else{
        	$this->objPHPMailer->Body = $body;
        }
        
        $status = $this->objPHPMailer->Send();
        if ($status) {
            return true;
        }
        
        return $this->objPHPMailer->ErrorInfo;
        
        while (true) {
        	$tmpSendResult = $this->objPHPMailer->Send();
	        if ($tmpSendResult) {
				return $tmpSendResult;
	        }
	        $retry_times--;
	        if ($retry_times < 0) {
	        	break;
	        }

	        sleep($retry_frequency);
        }
	}
}

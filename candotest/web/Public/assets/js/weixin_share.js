/**
 * 微信内置浏览器分享js(可自定义分享的内容，图片等)
 * @param {string} imgUrl
 * @param {string} lineLink
 * @param {string} descContent
 * @param {string} shareTitle
 * 
 * @author  pulingao
 * 
 * 调用方式:
 * 
 *  var imgUrl = 'http://wwwcdn.kimiss.net/misc/upload/a/image/2014/0708/112605_62204.jpg';
	var lineLink = 'http://misc.kimiss.com/weixin/dir_article/index?clinic=1';
	var descContent = "黑眼圈，眼袋，眼纹怎么办？";
	var shareTitle = '专家肌诊室-闺蜜网';
	create_share(imgUrl, lineLink, descContent, shareTitle)
 */


function shareFriend(imgUrl, lineLink, descContent, shareTitle, width, height) {
    WeixinJSBridge.invoke('sendAppMessage',{
        "img_url": imgUrl,
        "img_width": width,
        "img_height": height,
        "link": lineLink,
        "desc": descContent,
        "title": shareTitle
    }, function(res) {
    	_report('send_msg', res.err_msg);
	})
}

function shareTimeline(imgUrl, lineLink, descContent, shareTitle, width, height){
    WeixinJSBridge.invoke('shareTimeline', {
        "img_url": imgUrl,
        "img_width": width,
        "img_height": height,
        "link": lineLink,
        "desc": descContent,
        "title": shareTitle
    }, function(res){
        _report('timeline', res.err_msg);
    });
}

function shareWeibo(lineLink, descContent){
    WeixinJSBridge.invoke('shareWeibo', {
        "content": descContent,
        "url": lineLink,
    }, function(res){
        _report('weibo', res.err_msg);
    });
}


function create_share(imgUrl, lineLink, descContent, shareTitle, width, height){
	
	// 定义默认的宽高
	width = width ? width : 640;
	height = height ? height : 640;
	
	// 当微信内置浏览器完成内部初始化后会触发WeixinJSBridgeReady事件。
	document.addEventListener('WeixinJSBridgeReady', function onBridgeReady(){
	    // 发送给好友
	    WeixinJSBridge.on('menu:share:appmessage', function(argv){
	        shareFriend(imgUrl, lineLink, descContent, shareTitle, width, height);
	    });
	    
	    // 分享到朋友圈
	    WeixinJSBridge.on('menu:share:timeline', function(argv){
	        shareTimeline(imgUrl, lineLink, descContent, shareTitle, width, height);
	    });
	    
	    // 分享到微博
	    WeixinJSBridge.on('menu:share:weibo', function(argv){
	        shareWeibo(lineLink, descContent);
	    });
	}, false);
}

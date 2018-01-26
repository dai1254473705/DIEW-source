function formatTime(date) {
  var year = date.getFullYear()
  var month = date.getMonth() + 1
  var day = date.getDate()

  var hour = date.getHours()
  var minute = date.getMinutes()
  var second = date.getSeconds()


  return [year, month, day].map(formatNumber).join('/') + ' ' + [hour, minute, second].map(formatNumber).join(':')
}

/**
 * rpx转化为px
 */
function rpxToPx(rpx){
    var ratepx = 0;
    var px     = 0;
    try {
        var res = wx.getSystemInfoSync()
        ratepx = 750/res.windowWidth;
    } catch (e) {
        console.log(e);
    }
    px = rpx/ratepx;
    return px;
}

/**
 * 获取平台类型
 */
function getPlayForm(){
    try {
        var res = wx.getSystemInfoSync()
        return res.platform;
    } catch (e) {
        return null;
    }
}

/**
 * 字符串切割
 */
function strWXSlice(str, width, fontSize, lines){
    if(str == undefined || str == '' || str == null){
        return '';
    }
    // var str = '“”“”“”“”“”“”“”“”“”“”“”“”“”“”“”“”“”“”“”“”“”“”“”“';
    // var str = '你好你好你好你好你好你好你好你好你好你好你好你好你好你好你好你好你好你好';
    // var str = '111111111111111111111111111111111111111111111111111111111111111111111111111111';
    // var str = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
    // var str = 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
    // var str = '你好,,,,,,,,,,nihao年后啊哈哦哦哦案件,,,23425,,vkbkccbkcx啊哈哦啊哈哦';
    // var str = '你好年后啊，，，，，，哈哦哦哦年后啊哈哦哦哦案件23425啊哈哦啊哈哦';
    var platform  = getPlayForm();
    // var primary   = 1;
    var rate    = [];
    if(platform == 'android'){
        rate = [0.95,1.60,1.46,1.68,3.10];
    }else{
        rate = [0.90,1.85,1.85,1.85,2.10];
    }

    // console.log(platform);
    var fontSize  = rpxToPx(fontSize);
    // var extraPx   = (lines-primary)*fontSize;
    var width     = rpxToPx(width)*lines;
    var newStr    = str.toString();
    var strLength = 0;
    var resStr    = '';
    for(var i=0; i<newStr.length; i++){
        if(strLength <= width){
            if(str.charCodeAt(i) > 255){
                strLength += parseInt(fontSize)/rate[0];
            }else if(str.charCodeAt(i) >= 48 && str.charCodeAt(i) <= 57){  // 48~57  阿拉伯数字
                strLength += parseInt(fontSize)/rate[1];
            }else if(str.charCodeAt(i) >= 65 && str.charCodeAt(i) <= 90){  // 65~90大写字母
                strLength += parseInt(fontSize)/rate[2];
            }else if(str.charCodeAt(i) >= 97 && str.charCodeAt(i) <= 122){  // 97~122 小写字母
                strLength += parseInt(fontSize)/rate[3];
            }else{  // 其余字符
                if(str.charCodeAt(i) < 100){  // 说明是半角符号
                    strLength += parseInt(fontSize)/rate[4];
                }else{  // 说明是全角符号
                    strLength += parseInt(fontSize)/rate[0];
                }
            }
            resStr  += newStr.charAt(i);
        }else{
            resStr  += '…';
            break;
        }
    }
    return resStr;
}

function formatNumber(n) {
  n = n.toString()
  return n[1] ? n : '0' + n
}

module.exports = {
  formatTime: formatTime,
  strWXSlice: strWXSlice,
  getPlayForm: getPlayForm
}

// var url = 'http://dev.sytown.cn:8000/FrameWeb/FrameService/Api.ashx';
var url = 'https://api.sytown.cn/FrameWeb/FrameService/Api.ashx';
var isdirect = true;

function navigatetoPage(id,url){
  if (!isdirect) {
    return 
  }
  isdirect = false;
  wx.navigateTo({
    url: url+id,
    complete: function() {
       setTimeout(function(){
           isdirect = true;
       },1000);
    }
  })
}
function redirecttoPage(id,url){
  if (!isdirect) {
    return 
  }
  isdirect = false;
  wx.redirectTo({
    url: url+id,
    complete: function() {
      setTimeout(function(){
           isdirect = true;
       },1000);
    }
  })
}


module.exports = {
  url: url,
  navigatetoPage: navigatetoPage,
  redirecttoPage: redirecttoPage
}

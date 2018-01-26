//index.js
//首页

var app = getApp()
var api  = require('../../utils/api.js');
Page({
  onReady: function() {
    wx.setNavigationBarTitle({
      title: '尚医微健康'
    })
  },
  onShareAppMessage: function () {
    var title = '尚医微健康';
    var desc = '所有关于健康的知识';
    return {
      title: title,
      desc: desc,
      path: '/pages/index/index'
    }
  },
  data: {},
  //页面跳转处理函数
  jumpPage: function(e){
    var id = e.target.dataset.sid;
    var url = '/pages/list/list?sname='+e.target.dataset.sname+'&sid=';
    // 设置遮罩样式
    this.setData( { 
      displaystyle: 'block'
    });
    api.navigatetoPage(id,url);
    var that = this;
    setTimeout(function(){
      that.setData( { 
        displaystyle: 'none'
      });
    },1000);
  }
})

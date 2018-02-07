
/**
 * 接口调用公共js
 * @param eva 全局变量，用于设置共用信息
 * @param G   全局变量，保存get信息
 * @param CONF   全局变量，保存服务器设置信息
 */

var Base64 = require('./base64.js');

var Common = {
  config: {
    encryptKey: 'iUoYu23z8njUkeL',
  },
  hostDomain: 'https://www.hasoffer.com/cando/',
  debugHostDomain: 'http://www.cando.com/',
  debug: false, //域名是否开始debug，是：使用本地域名，否：使用https域名
  apiList: {
    login: 'login',
    pub_total: 'query/pub_total',
    add: 'query/add',
    detail: 'query/detail',
    history: 'history',
    my: 'my',
    payment: 'payment',
    callback: 'wechat/callback',
    make_cash: 'make_cash'
  },
  getDomain: function(){
    return this.debug ? this.debugHostDomain : this.hostDomain;
  },

  // post 方法
  post: function (url, data, completeCallback, failCallback) {
    // set token param
    if (data.token == undefined) {
      var userToken = wx.getStorageSync('user_token');
      data.token = userToken;
    }
    
    wx.request({
      url: url,
      method: "POST",
      dataType: "json",
      data: {
        'params': Base64.encode(JSON.stringify(data)),
      },
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      success: function (res){
        typeof completeCallback == 'function' && completeCallback(res.data);
      },
      fail: function(res){
        console.group("===Fail Start===");
        console.log(res);
        console.groupEnd();
        typeof failCallback == 'function' && failCallback(res);
      },
      complete: function(res){
        // console.group("===Complete Start===");
        // console.log(res);
        // console.groupEnd();
      }
    })
  },

  // get 方法
  get: function (url, data, completeCallback, failCallback) {
    // set token param
    if (data.token == 'undefined') {
      var userToken = wx.getStorageSync('user_token');
      data.token = userToken;
    }
    wx.request({
      url: url,
      method: "GET",
      dataType: "json",
      data: {
        'params': Base64.encode(JSON.stringify(data)),
      },
      header: { 
        'content-type': 'application/x-www-form-urlencoded'
      },
      success: completeCallback,
      fail: function (res) {
        console.group("===Fail Start===");
        console.log(res);
        console.groupEnd();
        typeof failCallback == 'function' && failCallback(res);
      },
      complete: function (res) {
        // console.group("===Complete Start===");
        // console.log(res);
        // console.groupEnd();
      }
    })
  }
}

// 导出模块
module.exports = Common; //最后加上这个
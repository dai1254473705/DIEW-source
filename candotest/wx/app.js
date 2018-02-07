//app.js
import wux from 'components/wux/wux';

var Common = require('./utils/common.js');

App({
  onLaunch: function () {
    //调用API从本地缓存中获取数据
    var logs = wx.getStorageSync('logs') || []
    logs.unshift(Date.now())
    wx.setStorageSync('logs', logs);

    // this.user.wxLogin();
  },

  // 全局信息
  globalData: {
    userInfo: null,
    loginCallbackNow: false
  },

  getUserInfo: function(){
    return this.user.getUserInfo();
  },

  // 用户信息处理
  user: {
    isLogin: function(){
      var userToken = wx.getStorageSync('user_token');
      return userToken ? true : false;
    },
    getUserInfo: function(){
      var userInfo = wx.getStorageSync('user_info');
      // getApp().globalData.userInfo = userInfo;
      return userInfo;
    },
    // getUserToken: function(){
    //   return wx.getStorageSync('user_token');
    // },

    // 微信登录
    wxLogin: function (cb){
      var that = this;
      var post = {};
      
      // 未登录，调用微信登录，已登录，设置回调信息
      if (!this.isLogin()) {

        //调用登录接口
        wx.login({
          success: function (data) {
            post.code = data.code;
            wx.getUserInfo({
              success: function (res) {

                // 检验用户，换取token
                post.user = JSON.stringify(res);
                that._getServerToken(post, cb);
                getApp().globalData.userInfo = res.userInfo;
                
                // 登录之后的回调，注册一个定时器，等到token设置了之后再请求
                var loginInt = setInterval(function () {
                  if (getApp().globalData.loginCallbackNow) {
                    clearInterval(loginInt);

                    // 登录之后的回调
                    var callbackData = res.userInfo;
                    typeof cb == 'function' && cb(callbackData);
                  }
                }, 100);
                
              }
            })
          }
        });

      }else{
        // 登录之后的回调
        var callbackData = that.getUserInfo();
        typeof cb == 'function' && cb(callbackData);
      }

      

    },

    // 远程获取用户信息
    _getServerToken: function (data) {
      var $this = this;
      if (typeof data == undefined) {
        return false;
      }

      var url = Common.getDomain() + Common.apiList.login;
      Common.post(url, data, function (scData) {
        console.log(scData.status);
        if (scData.status) {
          wx.setStorageSync('user_info', scData.data);
          wx.setStorageSync('user_token', scData.data.token);
          
          getApp().globalData.loginCallbackNow = true;

        } else {
          $this.wxLogin();
        }

      }, function (seData) {
        //错误处理
      });

    }

  },
  // 加密模块
  ENC: require('./utils/encrypt.js'), // 暂时放弃加密，开发版使用明文传输数据

  // 通过scope来引入wux函数
  wux: (scope) => new wux(scope),
  
})
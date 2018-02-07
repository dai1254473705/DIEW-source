//index.js
//获取应用实例
const app = getApp()
let z = 0;
let start = {};
let end = {};

Page({
  data: {
    motto: 'Hello World',
    userInfo: {},
    hasUserInfo: false,
    canIUse: wx.canIUse('button.open-type.getUserInfo')
  },
  btnPhone: function () {
    wx.makePhoneCall({
      phoneNumber: '021-61992171' //仅为示例，并非真实的电话号码
    })
    app.globalData.touchCount.btnPhone++;
  },
  countTap:function(e){
    app.globalData.touchCount.countTap++;
  },
  countLongPress: function (e) {
    app.globalData.touchCount.countLongPress++;
  },
  countStartMove:function(e){
    let item = e.changedTouches[0];
    start = {
      "X": item.pageX,
      "Y": item.pageY
    };
  },
  countEndMove: function (e) {
    let item = e.changedTouches[0];
    
    end = {
      "X": item.pageX,
      "Y": item.pageY
    };
    z = Math.pow(Math.abs(end.X - start.X),2) + Math.pow(Math.abs(end.Y - start.Y),2);
    //移动的距离
    app.globalData.touchCount.moveLength.push(Math.sqrt(z));
    app.globalData.touchCount.currentPage = 'pages/index/index';
  },
  onShareAppMessage: function (res) {
    return {
      title: '佳享资产',
      path: '/page/jiaxiang/jiaxiang',
      success: function (res) {
        // 转发成功
        wx.showToast({
          title: '转发成功',
          icon: 'success',
          duration: 2000
        })
      },
      fail: function (res) {
        // 转发失败
        wx.showToast({
          title: '转发失败',
          icon: 'success',
          duration: 2000
        })
      }
    }
  },
  //事件处理函数
  // bindViewTap: function() {
  //   wx.navigateTo({
  //     url: '../logs/logs'
  //   })
  // },
  onLoad: function () {
    if (app.globalData.userInfo) {
      this.setData({
        userInfo: app.globalData.userInfo,
        hasUserInfo: true
      })
    } else if (this.data.canIUse){
      // 由于 getUserInfo 是网络请求，可能会在 Page.onLoad 之后才返回
      // 所以此处加入 callback 以防止这种情况
      app.userInfoReadyCallback = res => {
        this.setData({
          userInfo: res.userInfo,
          hasUserInfo: true
        })
      }
    } else {
      // 在没有 open-type=getUserInfo 版本的兼容处理
      wx.getUserInfo({
        success: res => {
          app.globalData.userInfo = res.userInfo
          this.setData({
            userInfo: res.userInfo,
            hasUserInfo: true
          })
        }
      })
    }
  },

  getUserInfo: function(e) {
    console.log(e)
    app.globalData.userInfo = e.detail.userInfo
    this.setData({
      userInfo: e.detail.userInfo,
      hasUserInfo: true
    })
  }
})

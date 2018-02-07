//index.js
//获取应用实例
const app = getApp()

Page({
  data: {
    motto: 'Hello World',
    userInfo: {},
    hasUserInfo: false,
    bidData:{},
    recordsTotal:0,
    pageIndex:1,
    pageSize:5,
    canIUse: wx.canIUse('button.open-type.getUserInfo')
  },
  //事件处理函数
  bindViewTap: function() {
    wx.navigateTo({
      url: '../logs/logs'
    })
  },
  //获取标的列表
  getBidList:function(){
    wx.request({
      url: "http://api.xiaobaijinrong.com/web/v1/api/0/bid/list",
      method: "POST",
      dataType: "json",
      data:{
        'pageIndex': this.data.pageIndex,
        'pageSize': this.data.pageSize,
        'queryType': '0'
      },
      header: {
        'content-type': 'application/json'
      },
      success: res => {
        console.log(res);
        if (res.data.retCode === 'SUCCESS') {
          wx.showToast({
            title: '成功',
            icon: 'success',
            mask:'true',
            duration: 2000
          })
          this.setData({ bidData: res.data.data.records, recordsTotal: res.data.data.recordsTotal });
        }
      },
      fail: res => {
        console.group("===Fail Start===");
        wx.showToast({
          title: '失败',
          icon: 'success',
          mask: 'true',
          duration: 2000
        })
      },
      complete: res => {
        wx.hideLoading();
        wx.stopPullDownRefresh();
        console.log("request end");
      }
    })
  },
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
    wx.showLoading({
      title: '加载中……',
      mask: 'true'
    })
    this.getBidList();
  },
  
  onPullDownRefresh: function () {
    // Do something when pull down.
    console.log(this.data.recordsTotal / this.data.pageSize);
    wx.showLoading({
      title: '加载中……',
      mask: 'true'
    })
    if ((this.data.recordsTotal / this.data.pageSize) < this.data.pageIndex){
      console.log("没有更多");
      wx.hideLoading();
      wx.showToast({
        title: '没有更多了',
        icon: 'success',
        mask: 'true',
        duration: 2000
      })
    }else{
    this.setData({pageIndex:this.data.pageIndex+1 })
      console.log("下拉刷新");
      this.getBidList();
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

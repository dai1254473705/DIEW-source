// shouru.js
var Common = require('../../utils/common.js');
var app = getApp();
var _this;

Page({

  /**
   * 页面的初始数据
   */
  data: {
    userInfo: {},
    income: {}
  },
  
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
      _this = this;
      
      // 初始化弹出层
      _this.$wuxToast = app.wux(this).$wuxToast;

      // 加载中
      wx.showLoading({
        title: '加载中...',
        mask: true,
      });
      
      // 设置展示值
      _this.setData({
        userInfo: app.user.getUserInfo()
      });

      // 登录成功后，获取信息
      app.user.wxLogin(function (userData) {
        var url = Common.getDomain() + Common.apiList.my;
        Common.post(url, {}, function (res) {
          wx.hideLoading();

          if (!res.status) {
            _this.$wuxToast.show({
              type: 'cancel',
              timer: 2000,
              color: '#fff',
              text: res.message ? res.message : '操作错误',
              success: function () {
                console.log('已完成');
              }

            });
            return false;
          }

          // 设置展示值
          _this.setData({
            income: res.data
          });

        }, function(res){
          wx.hideLoading();
          console.log(res);
        });

      });


  },

  clickMakeCash:function(event){
    var makeCash = event.currentTarget.dataset.cash;
    if (makeCash <= 0){
      return false;
    }

    var url = Common.getDomain() + Common.apiList.make_cash;
    Common.post(url, {make_cash:makeCash}, function(res){
      _this.$wuxToast.show({
        type: 'success',
        timer: 1500,
        color: '#fff',
        text: '提现成功',
        success: function () {
          _this.onLoad();
        }

      });
    }, function(){

    });
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
  
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
  
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {
  
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
    this.onLoad();
    wx.stopPullDownRefresh();
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
  
  },

  /**
   * 用户点击右上角分享
   */
  // onShareAppMessage: function () {
  
  // }

})
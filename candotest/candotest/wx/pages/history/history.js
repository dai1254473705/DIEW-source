var Common = require('../../utils/common.js');

//获取应用实例
var app = getApp();
var _this = null;
var allPageData = [];
var page = 1;
var is_next = true;

Page({
  data:{
    list: [],
    scrollTop: 0,
    scrollHeight: 0
  },
  /**
   * 页面相关事件处理函数--监听页面加载
   */
  onLoad: function () {
    _this = this;

    // 初始化弹出层
    this.$wuxToast = app.wux(this).$wuxToast;

    // 加载中
    wx.showLoading({
      title: '加载中...',
      mask: true,
    });

    // 登录成功后，获取信息
    allPageData = [];
    page = 1;
    is_next = true;
    app.user.wxLogin(this._callback.getHistory);

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
    var userData = app.getUserInfo();
    
    // 上拉加载更多
    if(is_next){
      wx.showLoading({
        title: '加载中...',
        mask: true,
      });

      setTimeout(function () {
        _this._callback.getHistory(userData);
      }, 500);
    }
    
  },

  // 页面内部的回调函数
  _callback: {
    getHistory: function (userData){

      // 无更多数据时
      if (!is_next){
        wx.hideLoading();
        _this.$wuxToast.show({
          type: 'forbidden',
          timer: 3000,
          color: '#fff',
          text: '暂无更多数据',
          success: function () {
            console.log('已完成');
          }
        });
        return false;
      }
      
      // 设置列表值
      var url = Common.getDomain() + Common.apiList.history + '?p=' + page;
      Common.post(url, {}, function (res) {
        wx.hideLoading();
        var result = res.status ? res.data.list : [];

        // 无数据
        if (!result.length) {
          _this.$wuxToast.show({
            type: 'cancel',
            timer: 3000,
            color: '#fff',
            text: '暂无更多数据',
            success: function () {
              console.log('已完成');
            }
          });
          return false;
        }

        // 循环赋值，做成一个大数组来渲染
        for (var resi in result) {
          allPageData.push(result[resi]);
        }
        // 判断是否需要page++
        (res.data.ajax_url.down_page != undefined && res.data.ajax_url.down_page != '') ? page++ : (is_next = false);
        
        _this.setData({
          list: allPageData
        });

      }, function(res){

        wx.hideLoading();
        _this.$wuxToast.show({
          type: 'cancel',
          timer: 3000,
          color: '#fff',
          text: '未请求到数据，请刷新重试！',
          success: function () {
            console.log('已完成');
          }
        });

      });
    }

  },

  //事件处理函数
  goDetail: function (event) {
    var dataSet = event.currentTarget.dataset
    if(!dataSet.id){
      return false;
    }
    
    wx.navigateTo({
        url: '../detail/detail?id=' + dataSet.id
    })
  },



})
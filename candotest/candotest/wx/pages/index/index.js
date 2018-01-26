//////////////////////////// index.js

var Common = require('../../utils/common.js');

//获取应用实例
var app = getApp();
var g_total = 0, _this = null;
var pub_total = 3;

Page({
  data: {
    motto: 'Hello World',
    userInfo: {},
    pub_total: 0,
    is_share: false
  },
  
  //事件处理函数
  bindViewTap: function() {
    wx.navigateTo({
      url: '../logs/logs'
    })
  },
  go_detail_role:function(){
      wx.navigateTo({
          url: '../detail_role/detail_role'
      }) 
  },
  go_disclaimer : function(){
      wx.navigateTo({
          url: '../Disclaimer/Disclaimer'
      }) 
  },
  /**
   * 注册监听load事件
   */
  onLoad: function () {
    _this = this;

    // 初始化弹出层
    this.$wuxToast = app.wux(this).$wuxToast;

    _this.setData({
      userInfo: app.user.getUserInfo()
    })

    // 加载中
    wx.showLoading({
      title: '加载中...',
      mask: true,
    });

    // 登录成功后，获取信息
    app.user.wxLogin(_this._getPubTotal);

  },

  // 从服务器获取详细信息
  _getPubTotal: function (userData) {
    var url = Common.getDomain() + Common.apiList.pub_total;
    Common.post(
      url, //链接
      {}, //data
      function (res) { //成功回调
        wx.hideLoading();
        
        g_total = res.data.total;

        // 设置WXML展示值
        _this.setData({
          pub_total: res.data.total,
          is_share: res.data.enable_share
        });

      },

      function (res) { //失败回调
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
      }

    );

  },

  /**
   * 注册监听显示事件
   */
  onShow: function(){
    setTimeout(function(){
      wx.hideLoading();
    }, 300);
  },

  
  // form提交事件
  formSubmit: function (e) {
    var form = e.detail.value;
    // const _this = this;

    // 验证发布次数
    if (parseInt(g_total) == 0){
      _this.$wuxToast.show({
        type: 'text',
        timer: 1500,
        color: '#fff',
        text: '今天不能在发布了哦',
        success: function () {
          console.log('已完成')
        }
      });
      return false;
    }


    // 验证
    if(form.title == ''){
      _this.$wuxToast.show({
        type: 'text',
        timer: 1500,
        color: '#fff',
        text: '请填写标题信息',
        success: function(){
        	console.log('已完成')
        }
      });
      return false;
    }

    if (isNaN(form.price)){
      _this.$wuxToast.show({
        type: 'text',
        timer: 1500,
        color: '#fff',
        text: '请正确填写查询费用',
        success: function () {
          console.log('已完成')
        }
      });
      return false;
    }

    if (form.content == '') {
      _this.$wuxToast.show({
        type: 'text',
        timer: 1500,
        color: '#fff',
        text: '请务必填写付费内容',
        success: function () {
          console.log('已完成')
        }
      });
      return false;
    }

    // 加载中
    wx.showLoading({
      title: '加载中...',
      mask: true,
    });

    // 开始提交
    var url = Common.getDomain() + Common.apiList.add;
    Common.post(url,form, function (scData) {
      if (scData.status) {
        var inquire_id = scData.data.inquire_id;
        wx.hideLoading();
        setTimeout(function(){
          wx.redirectTo({
            url: '/pages/detail/detail?id=' + inquire_id,
          });
        }, 300);
        
      } else {

        wx.hideLoading();
        _this.$wuxToast.show({
          type: 'forbidden',
          timer: 1500,
          color: '#fff',
          text: scData.message,
          success: function () {}
        });
      }

    }, function (seData) {
      //错误处理
      wx.hideLoading();
      _this.$wuxToast.show({
        type: 'forbidden',
        timer: 1500,
        color: '#fff',
        text: "提交失败，请刷新页面重试！",
        success: function () { }
      });
    });

  },
})

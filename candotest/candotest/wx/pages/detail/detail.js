// detail.js
var Common = require('../../utils/common.js');
var MD5 = require('../../utils/md5.js');
var app = getApp(), _this;
var detailData, pageParams;

Page({

  /**
   * 页面的初始数据
   */
  data: {
    motto: 'Hello World',
    userInfo: app.user.getUserInfo(),
    inquire: {}
  },

  go_detail_role: function () {
    wx.navigateTo({
      url: '../detail_role/detail_role'
    })
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    _this = this;
    console.log(options);
    pageParams = options;

    // 初始化弹出层
    _this.$wuxToast = app.wux(this).$wuxToast;
    
    // 加载中
    wx.showLoading({
      title: '加载中...',
      mask: true,
    });

    // 登录成功后，获取信息
    app.user.wxLogin(_this._getDetail);

  },
  
  // 从服务器获取详细信息
  _getDetail: function (userData) {
    var url = Common.getDomain() + Common.apiList.detail;
    var postData = {
      inid: pageParams.id
    }
    if(pageParams.sh != undefined){
      postData.sh = pageParams.sh;
    }
    Common.post(
      url, //链接
      postData, //data
      function (res) { //成功回调
        wx.hideLoading();

        if (!res.status) {
          _this.$wuxToast.show({
            type: 'cancel',
            timer: 2000,
            color: '#fff',
            text: res.message,
            success: function () {
              console.log('已完成')
            }
          });
          return false;
        }

        /////////////// 设置页面值，用于分享 ////////////
        detailData = res.data;

        // 隐藏分享
        if (!parseInt(detailData.is_allow_share)) {
          wx.hideShareMenu({
            success: function () {
              console.log('hide share menu success');
            }
          });
        }

        // 根据is_sign_code 判断，是否需要写入分享的hash_code
        if (detailData.is_sign_code){
          var storageName = 'share_code_inquire_' + detailData.id;
          var share_code = wx.getStorageSync(storageName);
          if(share_code == '' || share_code == null){
            wx.setStorageSync(storageName, detailData.sign_code);
          }
        }
        
        // 设置WXML展示值
        _this.setData({
          inquire: res.data
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

        wx.hideShareMenu({
          success: function () {
            console.log('hide share menu success');
          }
        });
      }

    );

  },

  // 去支付，调用统一下单接口，调起支付
  goToPay: function(event){
    // 加载中
    wx.showLoading({
      title: '加载中...',
      mask: true,
    });

    var url = Common.getDomain() + Common.apiList.payment;
    var sign_code_name = 'share_code_inquire_' + detailData.id;
    var postData = {
      id:detailData.id,
      debug: Common.debug,
      sign_code: wx.getStorageSync(sign_code_name)
    };

    // ####### 去支付，调用统一下单接口，调起支付 begin #######//
    Common.post(
      url,
      postData,
      function(res){  //获取prepay_id的成功回调
        wx.hideLoading();

        if(!res.status){
          _this.$wuxToast.show({
            type: 'cancel',
            timer: 3000,
            color: '#fff',
            text: res.message ? res.message : '数据异常，请重试',
            success: function () {
              console.log('已完成');
            }
          });
        }

        // 正确返回
        var appTuneUp = res.data;
        
        //##### 调起微信支付 begin #####//
        wx.requestPayment({
          'timeStamp': appTuneUp.timeStamp,
          'nonceStr': appTuneUp.nonceStr,
          'package': appTuneUp.package,
          'signType': 'MD5',
          'paySign': appTuneUp.sign,
          'success': function (res) { //微信支付成功

            console.group("pay success==");
            console.info(res);
            console.groupEnd();

            ///####### 同步回调服务端 begin #########//
            var payCallbackUrl = Common.getDomain() + Common.apiList.callback;
            var cbData = {
              code: appTuneUp.order_code,
              sign: MD5.hexMD5(appTuneUp.order_code + Common.config.encryptKey)
            };

            // 付款完成，点击完成时
            wx.showLoading({
              title: '确认支付结果...',
              mask: true,
            });

            Common.post(payCallbackUrl, cbData, function(res){
              wx.hideLoading();

              _this.$wuxToast.show({
                type: 'success',
                timer: 500,
                color: '#fff',
                text: '支付成功',
                success: function () {
                  setTimeout(function(){
                    _this.onLoad(pageParams);
                  }, 500);
                }
              });

            }, function(res){
              _this.$wuxToast.show({
                type: 'cancel',
                timer: 1500,
                color: '#fff',
                text: res.message ? res.message : '支付出现问题，请联系管理员',
                success: function () {}
              });
            });
            ///####### 同步回调服务端 end #########//

          },
          'fail': function (res) {  //微信支付错误
            console.group("pay fail==");
            console.log(res);
            console.groupEnd();

            _this.$wuxToast.show({
              type: 'cancel',
              timer: 3000,
              color: '#fff',
              text: '支付数据异常，请重试',
              success: function () {
                console.log('已完成');
              }
            });
          },
          'complete': function (res) {  //微信支付完成
            console.group("pay complete==");
            console.log(res);
            console.groupEnd();
          }
        });
        //##### 调起微信支付 end #####//

      },
      function(res){
        wx.hideLoading();
        if (!res.status) {
          _this.$wuxToast.show({
            type: 'cancel',
            timer: 3000,
            color: '#fff',
            text: '出现了错误',
            success: function () {
              console.log('已完成');
            }
          });
        }
      }
    );
    // ####### 去支付，调用统一下单接口，调起支付 begin #######//

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
  // onPullDownRefresh: function () {
  //   this.onLoad();
  //   wx.stopPullDownRefresh();
  // },

  /**
   * 页面上拉触底事件的处理函数
   */
  // onReachBottom: function () {
    
  // },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
    var path = '/pages/detail/detail?sh=' + encodeURIComponent(detailData.hash_code) + '&' + 'id=' + detailData.id;
    console.log(path);

    return {
      title: detailData.title,
      desc: '最具人气的YouCanDo, 你行的!',
      path: path,
      success: function(res){
        _this.$wuxToast.show({
          type: 'success',
          timer: 3000,
          color: '#fff',
          text: '分享成功',
          success: function () {
            console.log('已完成');
          }
        });
      }
    }
  }

});
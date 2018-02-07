Page({
  data:{},
  btnPhone:function(){
    wx.makePhoneCall({
      phoneNumber: '021-61992171' //仅为示例，并非真实的电话号码
    })
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
  onShow: function () {
    wx.getLocation({
      type: 'gcj02', //返回可以用于wx.openLocation的经纬度
      success: function (res) {
        var latitude = res.latitude
        var longitude = res.longitude
        console.log(res);
        wx.openLocation({
          latitude: latitude,
          longitude: longitude,
          scale: 28
        })
      }
    })
  }
})
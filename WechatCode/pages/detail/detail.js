//detail.js
//详情页
var api = require('../../utils/api.js');
var WxParse = require('../../utils/wxParse/wxParse.js');
Page({
  data: {
    content:[],
    labels:[],
    related:[],
    hideloadhud:false   
  },
  onLoad: function(option) {
    // console.log(option);
    this.aid = option.aid;
    this.getNet();
  },
  onReady: function() {
    this.onRefresh();
  },
  onShareAppMessage: function () {
    var title = this.data.content.NAME ? this.data.content.NAME : '尚医微健康 文章详情';
    var desc = this.data.content.Summary;
    var aid = this.aid;
    return {
      title: title,
      desc: desc,
      path: '/pages/detail/detail?aid='+aid
    }
  },
  onRefresh: function() {
    var that = this;
    wx.request({
      url: api.url,
      data: {
        "_type":"getnormal",
        "_datatype":"json",
        "_dataid":"ArticleDetailed_Select",
        "_param":{
            "ArticleID":that.aid
        }
      },
      method: 'POST', 
      success: function(res){
        console.log(res);
        if (res.statusCode ==200){
            that.pagetitle= !res.data.data.Article.NAME ? '知识详情':res.data.data.Article.NAME;
            wx.setNavigationBarTitle({
              title: that.pagetitle
            })
            var content = res.data.data.Article.Content.replace(/&quot;/g,'');
            WxParse.wxParse('articlecontent', 'html', content, that,12.5);
            if (res.data.data.Article.Label){
              var labels = res.data.data.Article.Label.split(",");
              that.setData({
                labels: labels
              })
            }

            that.setData({
              content: res.data.data.Article,
              related: res.data.data.RelatedArticle
            })
        }else{
            console.log(res.errMsg);
        }
      },
      fail: function() {
        that.getNet();
      },
      complete: function() {
        if (!that.data.hideloadhud) {
          that.setData({
            hideloadhud:true
          })
        }
      }
    })
  },
  getNet(){
    var that=this;
    wx.getNetworkType({
      success: function(res) {
        if (res.networkType != 'none'){
          that.setData({net:true})
        }else{
          that.setData({net:false})
        }
      },
      fail: function(){
        that.setData({net:false})
      }
    })
  },
  netrefresh: function(){
      this.setData({hideloadhud:false});
      this.onRefresh();
      this.getNet();
  },
  //页面跳转处理函数
  jumpPage: function(e){
    var id = e.currentTarget.dataset.aid;
    var url = '/pages/detail/detail?aid=';
    // 设置遮罩样式
    this.setData( { 
      displaystyle: 'block'
    });
    api.redirecttoPage(id,url);
    var that = this;
    setTimeout(function(){
      that.setData( { 
        displaystyle: 'none'
      });
    },1000);
  }
})

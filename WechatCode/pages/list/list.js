//list.js
//文章列表页
var api  = require('../../utils/api.js');
var util = require('../../utils/util.js');
Page({
  data: {
    list:[],
    refresh:true,
    loading:false,
    hideloadhud:false,
    loadtext:'',
    endmore:false
  },
  onLoad: function(option) {
    this.pagetitle = option.sname;
    this.sid = option.sid;
    this.getNet();
  },
  onReady: function() {
    wx.setNavigationBarTitle({
      title: this.pagetitle
    });
    this.onRefresh();
  },
  onShareAppMessage: function () {
    var title = this.pagetitle ? this.pagetitle : '尚医微健康 列表页';
    var desc = '有关'+this.pagetitle+'的知识';
    var sid = this.sid;
    return {
      title: title,
      desc: desc,
      path: '/pages/list/list?sid='+sid
    }
  },
  onPullDownRefresh: function(){
    this.setData({refresh:true});
    this.onRefresh();
    if (!this.data.refresh) {
      wx.stopPullDownRefresh;
    }
  },
  onReachBottom: function(){
    this.loadMore();
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
  onRefresh: function(){
    if (!this.data.refresh) return;
    var that = this;
    wx.request({
      url: api.url,
      data: {
        "_type":"getnormal",
        "_datatype":"json",
        "_dataid":"IndexSectionList_Select",
        "_param":{
            "SectionID":that.sid,
            "pageSize":8,
            "pageIndex":1
        }
      },
      method: 'POST', 
      success: function(res){
        var getPlayForm = util.getPlayForm();
        var lineHeight  = '';
        if(getPlayForm != 'android'){
            lineHeight = 'line-height:26rpx;';
        }
        if (res.statusCode ==200){
          var length = res.data.data.IndexSectionBusinessList.length;
              for(var i=0; i<length; i++){
                res.data.data.IndexSectionBusinessList[i].ShortName = util.strWXSlice(res.data.data.IndexSectionBusinessList[i].Name,433,34,2);
              }
          that.pageindex =1;
            that.setData({
              list: res.data.data.IndexSectionBusinessList,
              lineHeight:lineHeight,
              endmore:false
            })
        }else{
            console.log(res.errMsg);
        }
      },
      fail: function() {
        that.getNet();
      },
      complete: function() {
        // 禁止再次自动刷新调用
        that.setData({refresh:false});
        // 动作执行完成之后，停止下拉刷新
        wx.stopPullDownRefresh();
        // 加载状态及底部内容文本的显示变化
        if (!that.data.hideloadhud) {
          that.setData({
            hideloadhud:true,
            loadtext:'加载更多'
          })
        }
      }
    })
  },
  loadMore: function(){
    if (this.data.endmore) return;
    this.setData({
      loading:true,
      loadtext:'加载中...'
    })
    this.pageindex++;
    var that = this;
    wx.request({
      url: api.url,
      data: {
        "_type":"getnormal",
        "_datatype":"json",
        "_dataid":"IndexSectionList_Select",
        "_param":{
            "SectionID":that.sid,
            "pageSize":8,
            "pageIndex":that.pageindex
        }
      },
      method: 'POST', 
      success: function(res){
        // console.log(res);
        var getPlayForm = util.getPlayForm();
        var lineHeight  = '';
        if(getPlayForm != 'android'){
            lineHeight = 'line-height:26rpx;';
        }
        if (res.statusCode ==200 ){
            if (res.data.data.IndexSectionBusinessList.length >0){
              var length = res.data.data.IndexSectionBusinessList.length;
              for(var i=0; i<length; i++){
                res.data.data.IndexSectionBusinessList[i].ShortName = util.strWXSlice(res.data.data.IndexSectionBusinessList[i].Name,433,34,2);
              }
              that.setData({
                list: that.data.list.concat(res.data.data.IndexSectionBusinessList),
                lineHeight:lineHeight,
                loadtext:'加载更多'
              })
            }else{
              that.setData({
                loadtext:'已经到底了',
                endmore:true
              })
            }
        }else{
            console.log(res.errMsg);
        }
      },
      fail: function() {
        that.getNet();
      },
      complete: function() {
        that.setData({
          loading:false
        })
      }
    })
  },
  netrefresh: function(){
      this.setData({
        refresh:true,
        hideloadhud:false
      });
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
    api.navigatetoPage(id,url);
    var that = this;
    setTimeout(function(){
      that.setData( { 
        displaystyle: 'none'
      });
    },1000);
  }
})

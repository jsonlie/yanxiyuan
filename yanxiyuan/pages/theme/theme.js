var app = getApp();
var utils = require('../../utils/util.js');
Page({
    data: {
      host: app.globalData.host + 'applet/',
      isLoadMore:true,
      pageNumP: 1,//个人页
      pageNumO: 1,//公开页
      comType: 1,
      comList: [],
      switch_name: '公开',
    },
    onReady: function () {
      wx.setNavigationBarTitle({
        title: '蹊径'
      })
    },
    //开关处理
    switchChange: function (e) {
      if (e.detail.value) {
        this.setData({
          comType: 1,
          isLoadMore: true,
          switch_name: '公开'
        })
      } else {
        this.setData({
          comType: 0,
          isLoadMore: true,
          switch_name: '个人'
        })
      }
      this.onLoad();
    },

    onLoad: function () {
      var that = this;
      var comType = that.data.comType;
      wx.request({
        url: app.globalData.host + app.globalData.apiUrl + 'getComList/' + comType,
        headers: {
          'Content-Type': 'application/json'
        },
        data: {
          'session_key': wx.getStorageSync("session_key")
        },
        success: function (res) {
          that.setData({
            comList: res.data
          })
        }
      })
    },

    loadMore:function(){
      var that = this;
      var comType = that.data.comType;
      if (comType){//公开页
        var cutNum = that.data.pageNumO + 1;
      }else{//个人页
        var cutNum = that.data.pageNumP + 1;
      }
      wx.request({
        url: app.globalData.host + app.globalData.apiUrl + 'getComList/' + comType + '/' + cutNum,
        headers: {
          'Content-Type': 'application/json'
        },
        data: {
          'session_key': wx.getStorageSync("session_key")
        },
        success: function (res) {
          if (comType){//公开页
            that.setData({
              pageNumO: that.data.pageNumO + 1,
            })
          }else{//个人页
            that.setData({
              pageNumP: that.data.pageNumP + 1,
            })
          }
          that.setData({
            comList: that.data.comList.concat(res.data),
            isLoadMore: res.data.length > 0
          })
        }
      })
    },
})
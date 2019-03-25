var app = getApp();
var utils = require('../../utils/util.js');
Page({
  data: {
    host: app.globalData.host + 'applet/',
    user_list: [],//用户详情
    baby_detail: [],//宝贝详情
    baby_imgs: [],//宝贝图像
    headimg: [],//用户头像
    Num: 0,//第几个宝贝
  },
  onReady: function () {
    wx.setNavigationBarTitle({
      title: '我的家园'
    })
  },
  //选择器
  bindPickerChange:function(e){
    this.setData({
      Num: e.detail.value,
      baby_detail: this.data.user_list.baby_list[e.detail.value]
    })
    console.log(this.data.baby_detail)
  },

  onLoad: function (options) {
    var that = this
    wx.request({
      url: app.globalData.host + app.globalData.apiUrl + 'getUserDetail',
      headers: {
        'Content-Type': 'application/json'
      },
      data: {
        'session_key': wx.getStorageSync("session_key")
      },
      success: function (res) {
         that.setData({
           user_list: res.data,
           baby_detail: res.data.baby_list[0],
           baby_imgs: res.data.baby_imgs,
           headimg: that.data.headimg.concat(res.data.headimgurl),//将字符串推入数组
         })
      }
    })
  },
  //点击预览图片(宝宝)
  showPic: function (e) {
    wx.previewImage({
      current: e.target.dataset.src,//当前预览图片url（完整url地址）
      urls: this.data.baby_imgs,//所有图片url列表（完整url地址）
    })
  },
  //点击预览图片(用户)
  showMmPic: function (e) {
    console.log(this.data.headimg)
    wx.previewImage({
      current: e.target.dataset.src,
      urls: this.data.headimg,
    })
  },

})

var app = getApp();
var utils = require('../../utils/util.js');
Page({
  data: {
    host: app.globalData.host + 'applet/',
    details:[],
    img_file_list:[]
  },
  onReady: function () {
    wx.setNavigationBarTitle({
      title: '内容详情'
    })
  },
  onLoad: function (options) {
    var that = this
    wx.request({
      url: app.globalData.host + app.globalData.apiUrl + 'getComDetail/' + options.com_id,
      headers: {
        'Content-Type': 'application/json'
      },
      success: function (res) {
         that.setData({
           details: res.data.data,
           img_file_list: res.data.data.img_file_list,
         })
        //  console.log(that.data.details.img_list)
      }
    })
  },
  //点击预览图片
  showPic:function(e){
    wx.previewImage({
      current: e.target.dataset.src,//当前预览图片url（完整url地址）
      urls: this.data.img_file_list,//所有图片url列表（完整url地址）
    })
  },

})
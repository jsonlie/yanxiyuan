//获取应用实例
const app = getApp();

Page({
  data: {
    host: app.globalData.host,
  },
  //事件处理函数
  bindViewTap: function() {
    // wx.navigateTo({
    //   url: '../logs/logs'
    // })
  },
  
  onLoad: function (options) {
    var that = this;
     
   
  },

  //设置授权
  setAuth: function (e) {
    wx.openSetting({
      success: function (res) {
        console.log('设置成功')
        res.authSetting = {
          "scope.userInfo": true,
        }
        //重新登录
        app.getSession();
        //跳转至首页
        wx.switchTab({
          url: '/pages/index/index',
        })
      },
      fail: function (res) {
        console.log('设置失败')
      }
    })
  },

})

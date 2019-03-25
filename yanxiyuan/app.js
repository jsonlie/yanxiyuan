//app.js
App({
  onLaunch: function () {
    //初始化完成时，全局触发一次
    this.getSession();
    this.refresh();
    // let that = this;
    //检查session_key是否过期
    // wx.checkSession({
    //   success:function(){
    //     console.log('有效')
    //   },
    //   fail:function(){
    //     console.log('无效')
    //     that.getSession();
    //     that.refresh();
    //   }
    // })

  },
  
  //获取session
  getSession:function(){
    var that = this;
    wx.login({
      success:function(res){
        if(res.code){
          //获取用户信息
          wx.getUserInfo({
            success:function(info){
              that.globalData.userInfo = info.userInfo;
              //发起网络请求
              wx.request({
                url: that.globalData.host + that.globalData.apiUrl + 'login',
                data: {
                  code: res.code,
                  userInfo: info.userInfo
                },
                success: function (res) {
                  //将session_key存入缓存,用于后面请求验证
                  wx.setStorageSync("session_key", res.data.data.session_key);
                },
                fail: function (errMsg) {
                  //console.log(errMsg);
                }
              })
            },
            fail:function(res){
              //跳转至提示页面
              wx.reLaunch({
                url: '/pages/tips/tips',
              })
            }
          })          
        }else{
          console.log("登录失败！"+res.errMsg);
        }
      }
    })
  },

  //定时任务，每隔20分钟刷新session
  refresh:function(){
    var that = this;
    setInterval(that.getSession,20*60*1000);
  },

  globalData:{
    host: "http://www.hbmpacc.cn/",
    apiUrl: "applet/applet/Api/",
    userInfo:null,
  }
})
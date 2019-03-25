var app = getApp();
var base_url = app.globalData.host;

function formatTime(date) {
  var year = date.getFullYear()
  var month = date.getMonth() + 1
  var day = date.getDate()

  var hour = date.getHours()
  var minute = date.getMinutes()
  var second = date.getSeconds()


  return [year, month, day].map(formatNumber).join('/') + ' ' + [hour, minute, second].map(formatNumber).join(':')
}

function formatDate(date, split) {
  var year = date.getFullYear()
  var month = date.getMonth() + 1
  var day = date.getDate()
  return [year, month, day].map(formatNumber).join(split || '')
}


function formatNumber(n) {
  n = n.toString()
  return n[1] ? n : '0' + n
}

//加载中……
function load() {
  wx.showToast({
    title: "加载中...",
    icon: 'loading',
    duration: 2000
  })
}

//带取消弹窗
function showCanDialog(title, content, callback) {
  wx.showModal({
    title: title,
    content: content,
    showCancel: true,
    confirmColor: '#3cc51f',
    success: function (res) {
      if (res.confirm) {
        typeof callback == 'function' && callback();
        console.log('用户点击确定')
      }
    }
  })
}

//提示弹窗
function showDialog(title, content, callback) {
  wx.showModal({
    title: title,
    content: content,
    showCancel: false,
    confirmColor: '#3cc51f',
    success: function (res) {
      if (res.confirm) {
        typeof callback == 'function' && callback();
        console.log('用户点击确定')
      }
    }
  })
}

//请求方法
function request(url, postData,mothod, doSuccess, doFail, doComplete) {
  //先判断此时登录状态是否过期
  wx.checkSession({
    success:function(res){
      console.log('session_key还有效')
      wx.request({
        url: base_url + url,
        data: postData,
        method: mothod,
        success: function (res) {
          //检查后台的session_key是否已过期(code=2为超时，0为成功)
          if(res.data.code == 2){
            //重新登录
            goLogin();
          }
          if (typeof doSuccess == "function") {
            doSuccess(res);
          }
        },
        fail: function () {
          if (typeof doFail == "function") {
            doFail();
          }
        },
        complete: function () {
          if (typeof doComplete == "function") {
            doComplete();
          }
        }
      })

    },
    fail:function(res){
      //重新登录
      goLogin();
    }
  })

  
}

//登录
function goLogin(){
  wx.login({
    success:function(res){
      if(res.code){
        wx.request({
          url: base_url + app.globalData.apiUrl + 'login',
          data: {
            code: res.code
          },
          success: function (res) {
            //将session_key存入缓存,用于后面请求验证
            wx.setStorageSync("session_key", res.data.data.session_key);
          },
          fail: function (errMsg) {
            console.log(errMsg);
          }
        })
      }else{
        console.log("登录失败！" + res.errMsg);
      }
    }
  })
}


module.exports = {
  formatTime: formatTime,
  formatDate: formatDate,
  formatNumber: formatNumber,
  load:load,
  showCanDialog: showCanDialog,
  showDialog: showDialog,
  request: request,
  goLogin: goLogin,
}

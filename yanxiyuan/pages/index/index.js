//获取应用实例
var app = getApp();
var utils = require('../../utils/util.js');
Page({
  data: {
    eventMemoList: [],//备忘事件
    userInfo:null,
    switch_name:'公开',
    community:null,//输入框值
    com_id:null,//发表的足迹id
    imgArr:[]//上传的图片
  },
  onReady: function () {
    wx.setNavigationBarTitle({
      title: '言迹'
    })
  },
  //开关处理
  switchChange:function(e){
    if(e.detail.value){
      this.setData({
        switch_name: '公开'
      })
    }else{
      this.setData({
        switch_name: '个人'
      })
    }    
  },

  onLoad: function (options) {
    console.log(9999999999)
    console.log(99,wx.getStorageSync("session_key"))
    if (!wx.getStorageSync("session_key")){
      
    }
    var that = this;
    //判断是否授权
    wx.getUserInfo({
      success:function(res){
        that.setData({
          userInfo: res.userInfo,
        })   
        //备忘事件列表
        // wx.request({
        //   url: app.globalData.host + app.globalData.apiUrl + 'getEventList',
        //   data: {
        //     'session_key': wx.getStorageSync("session_key")
        //   },
        //   success: function (res) {
        //     that.setData({
        //       eventMemoList: res.data
        //     })
        //   },
        //   fail: function (res) { }
        // })
        utils.request(
          app.globalData.apiUrl + 'getEventList',
          { 'session_key': wx.getStorageSync("session_key")},
          'GET',
          function(res){
            that.setData({
              eventMemoList: res.data
            })
          },
        );
        
      },
      fail:function(res){
      }
    })
  },

  //发表(个人/公开)
  formSubmit:function(e){
    var that = this, content = e.detail.value.content, formId = e.detail.formId, types = e.detail.value.type;
    if (content.length == 0){
      utils.showDialog('提示','发表内容不可为空噢！');
    }else{
      //提交
      wx.request({
        url: app.globalData.host + app.globalData.apiUrl + 'addCommunity',
        method:'POST',
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        data: {
          'session_key': wx.getStorageSync("session_key"),
          'form_id': formId,
          'content': content,
          'type':types
        },
        success: function (res) {
          if(res.data.code == 100000){
            utils.showDialog('失败', res.data.err);
          }else{
            that.setData({
              com_id: res.data.data
            })
            //上传图片
            that.upload();

            utils.showDialog('成功', '发表成功！',function(){
              that.setData({
                community: null,//发表成功后清空输入框
                imgArr:[]
              })
            });
          }
        },
        fail: function (res) { }
      })
    }
  },

  //上传到服务器
  upload: function () {
    var that = this;
    for (var i = 0; i < this.data.imgArr.length; i++) {
      wx.uploadFile({
        url: app.globalData.host + app.globalData.apiUrl + 'upLoad',
        filePath: that.data.imgArr[i],
        name: 'file',
        formData:{
          'com_id': that.data.com_id,
          'res_type': 1//1-图片，2-语音
        },
        success: function (res) {
          console.log(222,res)
          if(res.data.code == 0){
            console.log('上传成功')
          }          
        }
      })
    }
  },
  //本地选择图片
  upimg: function () {
    var that = this;
    if (that.data.imgArr.length < 3) {
      wx.chooseImage({
        count: 3,//默认9张
        sizeType: ['original', 'compressed'],//original 原图，compressed 压缩图
        sourceType: ['album', 'camera'],//album 从相册选图，camera 使用相机
        success: function (res) {
          that.setData({
            imgArr: that.data.imgArr.concat(res.tempFilePaths)
          })
          console.log(111,that.data.imgArr)
        }
      })
    } else {
      utils.showDialog('提示', '最多上传三张图片！');
    }
  },

})

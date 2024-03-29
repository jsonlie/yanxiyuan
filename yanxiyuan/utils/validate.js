var app = getApp();
var base_url = app.globalData.host;

/*验证手机号码格式*/
function checknull(mobile) {
  var len = mobile.length;
  if (len <= 0) {
    return '不能为空';
  }
}


/*验证手机号码格式*/
function checkMobile(mobile) {
    var len = mobile.length;
    if (len <= 0) {
        return '请填写手机号码';
    }
    if (!/^1\d{10}$/.test(mobile)) {
        return '请您输入正确的手机号';
    }
}

/**
 * 获得字符长度
 */
function getCharLength(str) {
    var realLength = 0;
    var len = str.length;
    var charCode = -1;
    for (var i = 0; i < len; i++) {
        charCode = str.charCodeAt(i);
        if (charCode >= 0 && charCode <= 128) {
            realLength += 1;
        } else {
            // 如果是中文则长度加2
            realLength += 2;
        }
    }
    return realLength;
}

/*验证用户名*/
function checkUserName(username) {
    if (!/^(?!\d)(?!_)(?!.*?_$)[a-zA-Z0-9_\u4e00-\u9fa5]{2,12}$/.test(username)) {
        return '请输入2-12位字母、汉字开头的账号';
    }
}

/*验证真实名字 字符长度在2-7个字符之间*/
function checkName(realname) {
    if (!/^[\u4e00-\u9fa5·]{2,}$/.test(realname)) {
        return '请输入正确的姓名';
    }
}

function checkMinority(realname) {
    if (!/[\u4E00-\u9FA5]{2,5}(?:·[\u4E00-\u9FA5]{2,5})*/.test(realname)) {
        return '请输入正确的姓名';
    }
}

/*验证四位验证码*/
function checkAdminCaptcha(captcha) {
    if (!captcha || !/^\w{4}$/.test(captcha)) {
        return '请输入4位的验证码';
    }
}

//检测密码格式
function checkPassword(password) {
    if (password.length < 6) {
        return '密码太短';
    }
    if (password.length > 24) {
        return '密码太长';
    }

    if (/\s/.test(password)) {
        return '密码格式不能为空';
    }
}
/*验证后台登录密码格式*/
function checkAdminPassword(password) {
    var len = password.length;
    if (len <= 0) {
        return '请填写密码';
    }
    if (!/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z\.]{6,12}$/.test(password)) {
        return '请填写6-12位数字、字母组合的密码';
    }
}
/*验证验证码格式*/
function checkCaptchaFormat(captcha) {
    if (!captcha || !/^\d{6}$/.test(captcha)) {
        return '请填写六位数字验证码';
    }
}

function checkMoney(money) {
    var len = money.length;
    if (!len) {
        return '金额不能为空';
    }
    if (money <= 0) {
        return '金额必须大于0元';
    }
    if (!/^(-?\d+)(\.\d+)?$/.test(money)) {
        return '金额格式不正确';
    }
}

/*验证金额大于等于10元*/
function checkAMoney(money) {
    var leng = money.length;
    if (!leng) {
        return '金额不能为空';
    }
    if (money < 10) {
        return '金额必须大于等于10元';
    }
    if (!/^(-?\d+)(\.\d+)?$/.test(money)) {
        return '金额格式不正确';
    }
    if ((money % 10) != 0) {
        return '金额必须为10的倍数';
    }
}

/*建议银行卡格式*/
function bankCheck(bankno) {
    if (bankno.length < 15 || bankno.length > 19) {
        //$("#banknoInfo").html("银行卡号长度必须在15到19之间");
        return "银行卡号码错误";
    }
    var num = /^\d*$/;  //全数字
    if (!num.exec(bankno)) {
        //$("#banknoInfo").html("银行卡号必须全为数字");
        return "银行卡号码错误";
    }
    //开头6位
    var strBin = "10,18,30,35,37,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,58,60,62,65,68,69,84,87,88,94,95,98,99";
    if (strBin.indexOf(bankno.substring(0, 2)) == -1) {
        //$("#banknoInfo").html("银行卡号开头6位不符合规范");
        return "银行卡号码错误";
    }
    var lastNum = bankno.substr(bankno.length - 1, 1);//取出最后一位（与luhm进行比较）

    var first15Num = bankno.substr(0, bankno.length - 1);//前15或18位
    var newArr = new Array();
    for (var i = first15Num.length - 1; i > -1; i--) {    //前15或18位倒序存进数组
        newArr.push(first15Num.substr(i, 1));
    }
    var arrJiShu = new Array();  //奇数位*2的积 <9
    var arrJiShu2 = new Array(); //奇数位*2的积 >9

    var arrOuShu = new Array();  //偶数位数组
    for (var j = 0; j < newArr.length; j++) {
        if ((j + 1) % 2 == 1) {//奇数位
            if (parseInt(newArr[j]) * 2 < 9)
                arrJiShu.push(parseInt(newArr[j]) * 2);
            else
                arrJiShu2.push(parseInt(newArr[j]) * 2);
        }
        else //偶数位
            arrOuShu.push(newArr[j]);
    }

    var jishu_child1 = new Array();//奇数位*2 >9 的分割之后的数组个位数
    var jishu_child2 = new Array();//奇数位*2 >9 的分割之后的数组十位数
    for (var h = 0; h < arrJiShu2.length; h++) {
        jishu_child1.push(parseInt(arrJiShu2[h]) % 10);
        jishu_child2.push(parseInt(arrJiShu2[h]) / 10);
    }

    var sumJiShu = 0; //奇数位*2 < 9 的数组之和
    var sumOuShu = 0; //偶数位数组之和
    var sumJiShuChild1 = 0; //奇数位*2 >9 的分割之后的数组个位数之和
    var sumJiShuChild2 = 0; //奇数位*2 >9 的分割之后的数组十位数之和
    var sumTotal = 0;
    for (var m = 0; m < arrJiShu.length; m++) {
        sumJiShu = sumJiShu + parseInt(arrJiShu[m]);
    }

    for (var n = 0; n < arrOuShu.length; n++) {
        sumOuShu = sumOuShu + parseInt(arrOuShu[n]);
    }

    for (var p = 0; p < jishu_child1.length; p++) {
        sumJiShuChild1 = sumJiShuChild1 + parseInt(jishu_child1[p]);
        sumJiShuChild2 = sumJiShuChild2 + parseInt(jishu_child2[p]);
    }
    //计算总和
    sumTotal = parseInt(sumJiShu) + parseInt(sumOuShu) + parseInt(sumJiShuChild1) + parseInt(sumJiShuChild2);

    //计算Luhm值
    var k = parseInt(sumTotal) % 10 == 0 ? 10 : parseInt(sumTotal) % 10;
    var luhm = 10 - k;

    if (lastNum != luhm) {
        return "银行卡号码错误"
    }
}
//身份证验证算法
function identityCodeValid(code) {
    var city = { 11: "北京", 12: "天津", 13: "河北", 14: "山西", 15: "内蒙古", 21: "辽宁", 22: "吉林", 23: "黑龙江 ", 31: "上海", 32: "江苏", 33: "浙江", 34: "安徽", 35: "福建", 36: "江西", 37: "山东", 41: "河南", 42: "湖北 ", 43: "湖南", 44: "广东", 45: "广西", 46: "海南", 50: "重庆", 51: "四川", 52: "贵州", 53: "云南", 54: "西藏 ", 61: "陕西", 62: "甘肃", 63: "青海", 64: "宁夏", 65: "新疆", 71: "台湾", 81: "香港", 82: "澳门", 91: "国外 " };
    var tip = "";
    if (!code || !/^\d{6}(18|19|20)?\d{2}(0[1-9]|1[012])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)$/i.test(code)) {
        return "身份证号码错误";
    } else if (!city[code.substr(0, 2)]) {
        return "身份证号码错误";
    } else {
        //18位身份证需要验证最后一位校验位
        if (code.length == 18) {
            code = code.split('');
            //∑(ai×Wi)(mod 11)
            //加权因子
            var factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
            //校验位
            var parity = [1, 0, 'X', 9, 8, 7, 6, 5, 4, 3, 2];
            var sum = 0;
            var ai = 0;
            var wi = 0;
            for (var i = 0; i < 17; i++) {
                ai = code[i];
                wi = factor[i];
                sum += ai * wi;
            }
            var last = parity[sum % 11];
            if (parity[sum % 11] != code[17]) {
                return "身份证号码错误";
            }
        }
    }
}


function rmoney(s){   
   return parseFloat(s.replace(/[^\d\.-]/g, ""));   
} 

module.exports = {
    checkMobile:checkMobile,
    getCharLength:getCharLength,
    checkUserName:checkUserName,
    checkName:checkName,
    checkMinority:checkMinority,
    checkPassword:checkPassword,
    checkAdminCaptcha:checkAdminCaptcha,
    checkCaptchaFormat:checkCaptchaFormat,
    checkMoney:checkMoney,
    checkAMoney:checkAMoney,
    bankCheck:bankCheck,
    identityCodeValid:identityCodeValid,
    rmoney:rmoney,
    checknull: checknull,
}

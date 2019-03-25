<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 验证辅助函数
 * @author Holyshit <914076301@qq.com>
 * @todo   2014-4-20
 */

//------------------------------------------------------------------


/**
 * 是否是整数 (字符串类型的整数和数字类型的整数都算整数)
 *
 * @param All $val 需要判断的值
 * @return boolean
 */
function isInteger($val){
    return ctype_digit($val) || is_int($val);
}

/**
 * 验证是否是邮箱
 *
 * @param unknown $address 邮箱地址
 * @return boolean 是否是邮箱
 */
function isEmail($address){
    return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix",$address)) ? FALSE : TRUE;
}

/**
 * 验证是否是用户名
 *
 * @param unknown $username 用户名
 * @return boolean 是否是用户名
 */
function isUsername($username){
    $len = strlen($username);
    $mb_len = mb_strlen($username);

    $flag = false;
    // 首先检测长度
    if($mb_len > 2 && $mb_len <= 12){
    }elseif($len > 2 && $len < 18){
    }else{
        return $flag;
    }

    if(preg_match('/^(?!\d)(?!_)(?!.*?_$)[a-zA-Z0-9_\-\x{4e00}-\x{9fa5}]+$/u',$username)){
        $flag = true;
    }else{
        $flag = false;
    }

    if($flag == false) $flag = $this->isMobile($username);

    return $flag;
}

/**
 * 验证密码格式是否正确
 *
 * @param  string  $password 密码
 * @return boolean 密码格式是否正确
 */
function isPassword($password){
    $len = strlen($password);
    $flag = false;
    if($len < 6 || $len > 24){
        return $flag;
    }

    if(preg_match('/(^\d+$)|(^[a-zA-Z]+$)/',$password)){
        $flag = false;
    }else{
        $flag = true;
    }

    return $flag;
}

/**
 * 验证是否是ID
 *
 * @param Number $id 需要检测的数据
 * @return boolean 是否是ID
 */
function isId($id = ''){
    $flag = $this->isInteger($id);
    if($flag){
        if($id < 0 || $id > 999999999){
            $flag = false;
        }
    }
    return $flag;
}

/**
 * 验证是否是英文字符串
 *
 * @param String $val 需要检测的字符
 * @return Boolean 是否是英文字符串
 */
function isEnglishString($val){
    return preg_match('/^([a-zA-Z_]+)$/',$val);
}

/**
 * 验证是否是中文名字
 *
 * @param String $val 需要检测的名字
 * @return Boolean 是否是中文名字
 */
function isRealname($val){
    if(preg_match('/^[\x{4e00}-\x{9fa5}·]{2,}$/u',$val)){
        return true;
    }
    return false;
}

/**
 * 验证是否金额
 *
 * @param float $money 金额
 * @param boolean $checkTenfold 判断是否10的倍数，true时判断
 * @return boolean 是否金额
 */
function isMoney($money, $checkTenfold = false){
    $flag = false;
    if(preg_match('/^[0-9]+(\d)*(\.\d{1,2})?$/u',$money)){
        $flag = true;
    }else{
        $flag = false;
    }

    // 判断是否10的倍数
    if($flag == true && $checkTenfold == true){
        $flag = $money % 10 == 0 ? true : false;
    }
    return $flag;
}

/**
 * 验证是否金额并且10的倍数
 *
 * @param unknown $money 金额
 * @return boolean 是否金额并且10的倍数
 */
function isMoneyTen($money){
    $flag = false;
    if(preg_match('/^[1-9]+(\d)*(\.\d{1,2})?$/u',$money)){
        $flag = true;
    }else{
        $flag = false;
    }

    // 判断是否10的倍数
    if($flag == true){
        $flag = intval($money) == $money ? ($money % 10 == 0 ? true : false) : false;
    }
    return $flag;
}

/**
 * 验证是否是手机号码
 *
 * @param unknown $mobilephone
 * @return boolean
 */
function isMobile($mobilephone){
    return (!preg_match("/^1[0-9]{10}$/",$mobilephone)) ? FALSE : TRUE;
}

/**
 * 验证是否是订单号
 *
 * @param unknown $billno 订单号
 * @return number 是否是订单号
 */
function isBillno($billno){
    return 0;
}

/**
 * 验证是否是银行代码
 *
 * @param unknown $code 代码
 * @return boolean 是否是银行代码
 */
function isBankCode($code){
    $bankcodes = array(
        "ICBC",
        "ABC",
        "BOC",
        "CCB",
        "BOCOM",
        "CITIC",
        "GDB",
        "CMBC",
        "CMB",
        "HXB",
        "CEB",
        "CIB",
        "SPDB",
        "PSBC",
        "BCCB",
        "BOS",
        "PINGAN",
        "CBHB",
        "NJCB",
        "HZCB",
        "CZB",
        "BJRCB",
        "SRCB",
        "SDB"
    );
    return in_array($code,$bankcodes);
}

/**
 * 是否是安全字符串（只包含英文和数字）
 *
 * @param unknown $val 字符
 * @return number 是否是安全字符串
 */
function isSecurityString($val){
    return preg_match("/\w+/",$val);
}

?>
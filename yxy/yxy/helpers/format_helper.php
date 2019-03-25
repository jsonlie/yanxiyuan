<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 格式化辅助函数
 * @author Holyshit <914076301@qq.com>
 * @todo   2015-8-4
 */


/**
 * 格式化手机号
 * @param  string $mobile 手机号码
 * @return string 加密后的手机号码
 */
function formatMobile($mobile){
    return substr_replace($mobile, '****', 3, 4);
}

/**
 * 格式化银行卡号
 *
 * @param  string $card_no 银行卡号
 * @return string 加密后的银行卡号
 */
function formatBankCard($card_no){
    return str_repeat('**** ', 3) . mb_substr($card_no, -4);
}

/**
 * 格式化姓名
 * @param  string $realName 姓名
 * @return string 格式化后的姓名
 */
function formatRealName($realName){
    $count = mb_strlen($realName, 'UTF-8');

    if(! $count){
        return $realName;
    }

    // 2位：全变
    // 3位：保留第一位
    // 4位：保留首尾一位，中间加4个*
    // 5位以及以上：保留首尾两位，中间加4个*

    $result = '';

    switch($count){
        case 1: // 实际上不存在1位的，为了提高容错性
        case 2:
            $result = '**';
            break;

        case 3:
            $result = mb_substr($realName, 0, 1, 'UTF-8') . '**';
            break;

        case 4:
            $result = mb_substr($realName, 0, 1, 'UTF-8') . '****' . mb_substr($realName,-1,$count,'UTF-8');
            break;

        default:
            $result = mb_substr($realName,0,2,'UTF-8') . '****' . mb_substr($realName,-2,$count,'UTF-8');
            break;
    }

    return $result;
}

/**
 * 删除小数点后的0
 * @param  [type] $decimal [description]
 * @return [type]          [description]
 */
function formatDecimal($decimal) {
    $decimal = strval($decimal);

    if (strpos($decimal, '.') !== false) {
        $arr = array_reverse(str_split($decimal));


        foreach ($arr as $key => $char) {
            if ($char === '0') {
                unset($arr[$key]);
            } elseif ($char === '.') {
                if (! isset($arr[$key - 1])) {
                    unset($arr[$key]);
                }

                break;
            } else {
                break;
            }
        }

        return implode(array_reverse($arr));
    }

    return $decimal;
}

/**
 * 阿拉伯数字转中文大写数字
 * @param  string|int|float $ns 阿拉伯数字
 * @return string 中文大写数字
 */
function cny($num){
    $c1 = "零壹贰叁肆伍陆柒捌玖";
    $c2 = "分角元拾佰仟万拾佰仟亿";
    $num = round($num, 2);
    $num = $num * 100;
    if (strlen($num) > 10) {
        return "数据太长，没有这么大的钱吧，检查下";
    }
    $i = 0;
    $c = "";
    while (1) {
        if ($i == 0) {
            $n = substr($num, strlen($num)-1, 1);
        } else {
            $n = $num % 10;
        }
        $p1 = substr($c1, 3 * $n, 3);
        $p2 = substr($c2, 3 * $i, 3);
        if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
            $c = $p1 . $p2 . $c;
        } else {
            $c = $p1 . $c;
        }
        $i = $i + 1;
        $num = $num / 10;
        $num = (int)$num;
        if ($num == 0) {
            break;
        }
    }
    $j = 0;
    $slen = strlen($c);
    while ($j < $slen) {
        $m = substr($c, $j, 6);
        if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
            $left = substr($c, 0, $j);
            $right = substr($c, $j + 3);
            $c = $left . $right;
            $j = $j-3;
            $slen = $slen-3;
        }
        $j = $j + 3;
    }

    if (substr($c, strlen($c)-3, 3) == '零') {
        $c = substr($c, 0, strlen($c)-3);
    }
    if (empty($c)) {
        return "零元整";
    }else{
        if(substr($c, strlen($c)-3, 3) == '分'){//分为结尾时不需要 ’整'
            return $c;
        }else{
            return $c . "整";
        }
    }
}


/**
 * 获取时间数组
 * @param $time
 * @return array
 */
function getTimeTextFormat($time){
    $dataFormat = array();

    $dataFormat['day'] = floor($time/(24*60*60));
    $hour = $time%(24*60*60);

    $dataFormat['hour'] = floor($hour/(60*60));
    $minute = floor($hour%(60*60));

    $dataFormat['minute'] = floor($minute/60);
    $dataFormat['second'] = floor($minute%60);

    return $dataFormat;
}

/**
 * 获取日期的格式化天
 * @param $day  timestamp|date string
 * @return bool|string
 */
function getFormatDay($day){
    if(!is_numeric($day)){
        $day = strtotime($day);
    }
    if(empty($day)){
        return false;
    }
    $temp = date('Ymd', $day);
    
    if($temp == date('Ymd')){
        return '今天';
    }
    else if($temp == date('Ymd', strtotime(' +1 day'))){
        return '明天';
    }
    else if($temp == date('Ymd', strtotime(' +2 day'))){
        return '后天';
    }
    else if($temp == date('Ymd', strtotime(' +3 day'))){
        return '大后天';
    }
    else{
        return date('Y-m-d', $day);
    }
}

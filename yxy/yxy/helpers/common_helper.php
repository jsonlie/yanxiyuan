<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 全局公共函数库
 * @package     helpers
 * @author      cat <lynxcatdeng@gmail.com>
 * @create      2015-04-04
 * @description 用于存放公共函数的文件，文件内函数尽量不依赖于其他任何代码
 */

//------------------------------- 加密解密 start ------------------------------------

/**
 * 字符串加密
 * @param  string $str 需要加密的字符串
 * @return string 加密后的字符串
 */
function lcMd5($str){
    $prefix = defined('PASSWORD_PREFIX') ? PASSWORD_PREFIX : '';
    return md5($prefix . md5($str));
}

/**
 * discuz 加密算法
 * @param  string $string    需要加密或解密的key
 * @param  string $operation 加密或者解密
 * @param  string $key       加密或解密KEY
 * @param  int    $expiry    过期时间
 * @return string 加密或解密的数据
 */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0){
    $ckey_length = 4;
    $key = md5($key ? $key : PASSWORD_PREFIX);
    $keya = md5(substr($key,0,16));
    $keyb = md5(substr($key,16,16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string,0,$ckey_length) : substr(md5(microtime()),-$ckey_length)) : '';
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(substr($string,$ckey_length)) : sprintf('%010d',$expiry ? $expiry + time() : 0) . substr(md5($string . $keyb),0,16) . $string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0,255);
    $rndkey = array();

    for($i = 0;$i <= 255;$i++){
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for($j = $i = 0;$i < 256;$i++){
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for($a = $j = $i = 0;$i < $string_length;$i++){
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if($operation == 'DECODE'){
        if((substr($result,0,10) == 0 || substr($result,0,10) - time() > 0) && substr($result,10,16) == substr(md5(substr($result,26) . $keyb),0,16)){
            return substr($result,26);
        }else{
            return '';
        }
    }else{
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

//------------------------------- 加密解密 end ------------------------------------


//------------------------------- 其他 end ------------------------------------

/**
 * 解决nginx没有getallheaders方法
 */
if (!function_exists('getallheaders')) {
    function getallheaders() {
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

/**
 * 返回数据格式化
 * @param  int    $code 状态码
 * @param  string $val  错误信息或数据
 * @return array  格式化后的数据
 */
function dataFormat($code, $val = ''){
    $key = ($code == 0) ? 'data' : 'err';
    return array('code' => $code, $key => $val);
}

/**
 * 返回上页
 *
 * @param bool $isAdmin 是否后台
 */
function backReferer($isAdmin = false) {
    $CI =& get_instance();
    //获得返回上一页的路径
    $backUrl = $CI->input->post( 'backUrl' );
    $backUrl = empty( $backUrl ) ? $CI->input->server( 'HTTP_REFERER' ) : $backUrl;

    if ( false === strpos( $backUrl, WEB_SITE_HTTP ) && false === strpos( $backUrl, WEB_SITE_HTTPS ) || $backUrl == current_url() || false !== strpos( $backUrl, 'login' ) || false !== strpos( $backUrl, 'register' ) ) {
        if ( $isAdmin ) {
            redirect(base_url('admin/index'));
        } else {
            redirect(base_url('index'));
        }
    } else {
        redirect( $backUrl );
    }

    exit;
}

/**
 * 获取配置项值
 * @param string $configName 配置项名
 * @param string $keyName    key名
 * @param mixed  $return     获取不到配置项值时要返回的值
 */
function getConfigVal($configName, $keyName, $return = '不存在') {
    $CI =& get_instance();
    $configArr = $CI->config->item($configName);

    if (isset($configArr[$keyName])) {
        $val = $configArr[$keyName];
    } else {
        $val = $return;
    }

    return $val;
}

/**
 * 系统消息数
 * @return array
 */
function sysMsgNum() {
    $CI =& get_instance();
    $CI->load->library('logs/log_lib');
    $errNum = $CI->log_lib->getErrNum();

    if (lang('code_success') == $errNum['code']) {
        $errNum = $errNum['data'];
    } else {
        $errNum = 0;
    }

    return $errNum;
}

/**
 * 设置TKD
 * @param  string $title
 * @param  string $keywords
 * @param  string $description
 * @return array
 */
function setTKD($title = '', $keywords = '', $description = ''){
    if (empty($title)) {
        //$title = '红狐理财';
        //$title = '红狐理财_已上线【银行存管】，年轻人首选理财品牌，安全可靠【官网】';
        $title = '红狐理财_已上线【银行存管】，年轻人首选投资品牌，合规稳健【官网】';
    }

    if (empty($keywords)) {
        //$keywords = '红狐理财,理财平台,网上理财,武汉投资理财【官网】';
        $keywords = '红狐理财,银行存管,红狐金融,红狐投资,理财平台,网上投资,武汉金融理财,武汉P2P,P2P理财,活期理财【官网】';
    }

    if (empty($description)) {
        //$description = '红狐理财提供安全稳健的P2P理财服务，10元起投，高达15%年化收益，百元红包任性送。理财产品“政企宝”，对接政府PPP项目，优化投资，助力民生基建。智选红狐，理财轻松！ ';
        //$description = '红狐理财是一家提供安全稳健理财产品的创新性金融服务平台,100元起投,年化收益高达13%。特有期限适中的定期产品「扑满」和灵活存取的活期产品「加薪」等多元化理财产品，产品灵活富有趣味,深受年轻人喜爱。';
        $description = '红狐理财是一家创新型的互联网金融服务平台，100元起投，年化收益8%-13%，特有的超短期产品【加薪】和期限多样的【扑满】产品深受广大用户喜爱。 ';
    }

    return array(
        'title'       => $title,
        'keywords'    => $keywords,
        'description' => $description
    );
}

/**
 * 获取摘要
 * @param  string $longStr 长字符串
 * @return string 截取前一部分的短字符串
 */
function summary($longStr) {
    $len = 20;

    if (mb_strlen($longStr) > $len) {
        $summary = mb_substr($longStr, 0, $len) . '...';
    } else {
        $summary = $longStr;
    }

    return $summary;
}

/**
 * 获取随机字符串
 * @param $length
 * @return null|string
 */
function getRandString($length){
    $str = null;
    $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($str_pol)-1;

    for($i=0;$i<$length;$i++){
        $str .= $str_pol[rand(0,$max)];
    }

    return $str;
}

/**
 * 动态异常处理方法
 * @param $error
 * @param $error_string
 * @param $filename
 * @param $line
 * @param $symbols
 * @throws Exception
 */
function errorHandler($error, $error_string, $filename, $line, $symbols){
    $error_no_arr = array(1=>'ERROR', 2=>'WARNING', 4=>'PARSE', 8=>'NOTICE', 16=>'CORE_ERROR', 32=>'CORE_WARNING', 64=>'COMPILE_ERROR', 128=>'COMPILE_WARNING', 256=>'USER_ERROR', 512=>'USER_WARNING', 1024=>'USER_NOTICE', 2047=>'ALL', 2048=>'STRICT');
    if(in_array($error,array(E_ERROR, E_WARNING, E_PARSE)))
    {
        throw new Exception($error_string);
    }
}

/**
 * @param $number 待格式化的数值
 * @param int $decimals 保留的小数位数
 * @param int $quartile //千分位--3  万分位--4
 * @param string $dec_point 小数点符号
 * @param string $thousands_sep 分位分割符
 * @return string 格式化后的字符串数字
 */
function numberFormat($number , $decimals = 0 ,$quartile = 4,  $dec_point = '.' , $thousands_sep = ','){
    $sign="";
    if($number<0){
        $sign="-";
        $number=abs($number);
    }
    $pointPos = strpos($number,'.',0);
    if($pointPos===false){
        $intStr = $number;
        $floatStr = '';
    }else{
        $intStr = substr($number,0,$pointPos);
        $floatStr = substr($number,$pointPos+1,$decimals);
    }
    $padLen = $decimals-strlen($floatStr);
    for($i=0;$i<$padLen;$i<$i++){
        $floatStr.='0';
    }
    $numArr = str_split(strrev($intStr),$quartile);
    $number = $sign.strrev(join($thousands_sep,$numArr));
    if(strlen($floatStr)){
        $number.= $dec_point.$floatStr;
    }
    return $number;
}

/**
 * 字符串转byte
 * @param $str
 * @return array
 */
function strToBytes($str){
    $asc = array();
    for($i = 0; $i < strlen($str); $i++){    //遍历每一个字符 用ord函数把它们拼接成一个php数组
        $asc[] = ord($str[$i]);
    }
    $bytes = array();
    foreach ($asc as $v){
        if($v > 127){
            $bytes[] = numToByte($v);
        }
        else{
            $bytes[] = $v;
        }
    }

    return $bytes;
}

/**
 * 数值转byte
 * @param $num
 * @return number
 */
function numToByte($num){
    $num = decbin($num);  //decbin 是php自带的函数，可以把十进制数字转换为二进制

    $num = substr($num, -8);      //取后8位
    $sign = substr($num, 0, 1);       //截取 第一位 也就是高位，用来判断到底是负的还是正的
    if($sign == 1)  //高位是1 代表是负数 ,则要减去256
    {
        return bindec($num) - 256; //bindec 也是php自带的函数，可以把二进制数转为十进制
    }
    else
    {
        return bindec($num);
    }
}

/**
 * 字符串转数组
 * @param $str
 * @return array
 */
function mbStrSplit($str){
    return preg_split('/(?<!^)(?!$)/u', $str);
}

/**
 * 对象转数组
 * @param $object
 * @return mixed
 */
function objectToArray($object){
    $object = json_decode(json_encode($object), true);
    return $object;
}

/**
 * unicode转中文
 * @param type $name
 * @return string
 */
// 将UNICODE编码后的内容进行解码，编码后的内容格式：\u56fe\u7247 （原始：图片）  
function unicode_decode($name)  
{  
    // 转换编码，将Unicode编码转换成可以浏览的utf-8编码  
    $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';  
    preg_match_all($pattern, $name, $matches);  
    if (!empty($matches))  
    {  
        $name = '';  
        for ($j = 0; $j < count($matches[0]); $j++)  
        {  
            $str = $matches[0][$j];  
            if (strpos($str, '\\u') === 0)  
            {  
                $code = base_convert(substr($str, 2, 2), 16, 10);  
                $code2 = base_convert(substr($str, 4), 16, 10);  
                $c = chr($code).chr($code2);  
                $c = iconv('UCS-2', 'UTF-8', $c);  
                $name .= $c;  
            }  
            else  
            {  
                $name .= $str;  
            }  
        }  
    }  
    return $name;  
}


/**
 * 功能：把15位身份证转换成18位
 * @param string $idCard
 * @return string
 */
function getIDCard($idCard) {
    // 若是15位，则转换成18位；否则直接返回ID
    if (15 == strlen ( $idCard )) {
        $W = array (7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2,1 );
        $A = array ("1","0","X","9","8","7","6","5","4","3","2" );
        $s = 0;
        $idCard18 = substr ( $idCard, 0, 6 ) . "19" . substr ( $idCard, 6 );
        $idCard18_len = strlen ( $idCard18 );
        for($i = 0; $i < $idCard18_len; $i ++) {
            $s = $s + substr ( $idCard18, $i, 1 ) * $W [$i];
        }
        $idCard18 .= $A [$s % 11];
        return $idCard18;
    } else {
        return $idCard;
    }
}

/**
 * 浏览器友好的变量输出
 * @param mixed $var 变量
 * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
 * @param string $label 标签 默认为空
 * @param boolean $strict 是否严谨 默认为false (true显示数据类型)
 * @return void|string
 */
function dump($var, $echo = true, $label = null, $strict = false) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    } else {
        return $output;
    }
}

/*
 * @param   二进制流文件转换为图片保存到指定目录
 * @param   img_str
 * @param   path_name
 * @param   object
 * @return  path_url
 * * */
function saveStrToImg($img_str,$path_name,$object = null) {
    //创建目录
    $dir = date('Ymd');
    $rand = time().rand(10000,99999);
    if($object){
        $upload_path = UPLOAD_DIR .'/'. $path_name .'/' . $dir .'/'. $object .'/'. $rand;
    }else{
        $upload_path = UPLOAD_DIR .'/'. $path_name .'/'. $dir .'/'. $rand;
    }
    if (! is_dir($upload_path)) {
        mkdir($upload_path, 0777, true);
    }
    $path_url = $upload_path . '.jpg';
    //将数据流文件写入我们创建的文件内容中
    $ifp = fopen($path_url, 'wb');
    fwrite($ifp, base64_decode($img_str));
    fclose($ifp);
    return $path_url;
}

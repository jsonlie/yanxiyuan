<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
 *   小程序通信接口
 *   Created by Netbeans
 *   Author leslie <390940063@qq.com>
 *   Date 2018-04-08 14:13
 * */

class Wapp_base_lib {

    /**
     * CI超级对象
     * @var object
     */
    private $CI;

    /**
     * 小程序appid
     * @var string
     */
    private $appid = WAPP_ID_LYX;

    /**
     * 小程序appsecret
     * @var string
     */
    private $appsecret = WAPP_SECRET_LYX;

    /**
     * 小程序token
     * @var string
     */
    private $token = WAPP_TOKEN;

    /**
     * 构造方法,获取CI超级对象
     */
    public function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->library('utils/http');
        $this->CI->load->library('applet/user_lib');
        $this->CI->http->setHost('https://api.weixin.qq.com/');
    }

    /**
     * 获得API需要的TOKEN
     */
    public function getAccessToken() {
        $this->CI->http->setHost('https://api.weixin.qq.com/');
        $this->CI->http->setPath('cgi-bin/token');

        $params = array(
            'grant_type' => 'client_credential',
            'appid' => $this->appid,
            'secret' => $this->appsecret
        );

        // 获取保存在配置项的公众平台access_token
        $configToken = $this->CI->user_lib->getConfigValuesByKey('wapp_access_token');

        if (lang('code_success') == $configToken['code'] && !empty($configToken['data'])) {
            $configToken = $configToken['data'];

            if ($configToken['expires_in'] > ( time() + 3600 )) {  // access_token在有效时间内
                return $configToken;
            } else {
                $accessToken = $this->CI->http->get($params);
                $accessToken = json_decode($accessToken, TRUE);

                if (isset($accessToken['access_token'])) {
                    $this->CI->user_lib->updateConfigValuesByKey(
                            'wapp_access_token', array(
                        'values' => json_encode(array(
                            'access_token' => $accessToken['access_token'],
                            'expires_in' => $accessToken['expires_in'] + time()
                        )),
                        'utime' => time()
                            )
                    );

                    return $accessToken;
                } else {
                    return dataFormat(lang('code_get_token_err'));
                }
            }
        }
        $accessToken = $this->CI->http->get($params);
        $accessToken = json_decode($accessToken, TRUE);
        if (isset($accessToken['access_token'])) {
            $this->CI->user_lib->addConfigData(array(
                'name' => '小程序access_token配置',
                'key' => 'wapp_access_token',
                'values' => json_encode(array(
                    'access_token' => $accessToken['access_token'],
                    'expires_in' => $accessToken['expires_in'] + time(),
                )),
                'ctime' => time(),
            ));

            return $accessToken;
        } else {
            return dataFormat(lang('code_get_token_err'));
        }
    }

    /**
     * 发送模板消息
     */
    public function setTemplate($openid, $template_id, $page, $form_id, $data) {
        $result = $this->getAccessToken();
        $this->CI->http->setHost('https://api.weixin.qq.com/');
        $this->CI->http->setPath('cgi-bin/message/wxopen/template/send?access_token=' . $result['access_token']);

        $data = array(
            "touser" => $openid,
            "template_id" => $template_id,
            "page" => $page,
            "form_id" => $form_id,
            "color" => "#FF0000",
            "data" => $data,
//            "emphasis_keyword" => "keyword2.DATA"//默认放大的关键词
        );

        $res = $this->CI->http->post(json_encode($data), true);
        $res = json_decode($res, true);
        
        if (lang('code_success') == $res['errcode']) {
            $ret = dataFormat(lang('code_success'), $res['errmsg']);
        } else {
            $ret = dataFormat(lang('code_push_msg_err'), '发送模板消息失败');
        }

        return $ret;
    }

}

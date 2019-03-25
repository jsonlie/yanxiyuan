<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
 *   小程序消息推送接口
 *   Created by Netbeans
 *   Author leslie <390940063@qq.com>
 *   Date 2018-04-08 14:13
 * */

class Wapp_push_lib {

    /**
     * 模板id
     */
    const TPL_ID_COM_REMIND = '4HjKR5Vv5Drp5RvXxNWZ_K7MaU8ShXECygHhwPYWMpo';  // 留言评论提醒

    /**
     * CI超级对象
     * @var object
     */

    private $CI;

    /**
     * 构造方法，获取CI超级对象
     */
    public function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->library('wapp/wapp_base_lib');
        $this->CI->load->library('applet/user_lib');
    }

    /**
     * 留言评论提醒
     * @param $wapp_user_id
     * @param $condition     留言内容详情
     * @param $page
     * @param 
     * @return mixed
     */
    public function commentRemind($wapp_user_id, $condition, $page = '') {
        $userinfo = $this->CI->user_lib->getUserInfo($wapp_user_id);
        if (lang('code_success') == $userinfo['code']) {
            $openid = $userinfo['data']['openid'];
        } else {
            return $userinfo;
        }
        $form = $this->CI->user_lib->getFormID($wapp_user_id);
        if ($form['code'] == lang('code_fail')) {
            return dataFormat(lang('code_fail'), '没有可用的form_id用于推送消息');
        }
        //form_id只能用一次
        $this->CI->user_lib->updateFormID($form['data']['id']);
        $tplId = self::TPL_ID_COM_REMIND;

        $pushData = array(
            'keyword1' => array(
                'value' => $condition['content'],
                'color' => '#40d8af'
            ),
            'keyword2' => array(
                'value' => $condition['nickname'],
                'color' => '#40d8af'
            ),
            'keyword3' => array(
                'value' => date('Y-m-d H:i:s',$condition['ctime']),
                'color' => '#40d8af'
            ),
            'keyword4' => array(
                'value' => $condition['moment'],
                'color' => '#40d8af'
            ),
            'keyword5' => array(
                'value' => '你发表的足迹有人评论啦，快去看看吧！',
                'color' => '#000000'
            ),
        );
        
        return $this->CI->wapp_base_lib->setTemplate($openid, $tplId, $page, $form['data']['form_id'], $pushData);
    }

}

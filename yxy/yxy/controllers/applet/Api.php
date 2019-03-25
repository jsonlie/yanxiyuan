<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
 *   小程序接口
 *   Created by Netbeans
 *   Author leslie <390940063@qq.com>
 *   Date 2018-03-22 14:13
 * */

class Api extends CI_Controller {

    //当前用户id(所有需要用到user_id的请求接口，参数中必须带上session_key)
    private $cur_wapp_uid = null;
    //是否移动设备
    private $isMobile = true;
    //有效时间
    private $expire_m = 30;

    //构造函数
    public function __construct() {
        parent::__construct();

        //后面考虑存数据表中（IOPS高时可能会有效率问题，但目前不存在
        $this->load->library(array('applet/user_lib', 'applet/community_lib','wapp/wapp_push_lib'));

        if (!$this->agent->is_mobile()) {  // 非移动端
            $this->isMobile = false;
        }

        //处理用户登录
        $session_key = isset($_REQUEST['session_key']) ? $_REQUEST['session_key'] : null;
        if (!empty($session_key)) {
            $storage = $this->community_lib->getStorageByKey($session_key);
            if ($storage['code'] == lang('code_success')) {
                $this->cur_wapp_uid = $storage['data']['s_value'];
                //刷新有效时间
                $updata = array(
                    'expire_time' => time() + 60 * $this->expire_m
                );
                $this->community_lib->editStorage(array('s_key' => $storage['data']['s_key']), $updata);
            } else {
                $displayConfig = array(
                    'display' => 'json'
                );
                $this->display->view($displayConfig, dataFormat(lang('code_login_timeout'), '登录超时！')); //code=2
                exit;
            }
        }
    }

    /**
     * 小程序登录
     * @return mixed
     */
    public function login() {
        $displayConfig = array(
            'display' => 'json'
        );

        $code = $this->input->get('code');
        $userInfo = json_decode($this->input->get('userInfo'), TRUE);
        if (empty($code) || empty($userInfo)) {
            return $this->display->view($displayConfig, dataFormat(lang('code_fail'), '登录失败！'));
        }
        $res = $this->user_lib->getUserSession($code);
        if ($res['code'] != lang('code_success')) {
            return $this->display->view($displayConfig, dataFormat(lang('code_fail'), '登录失败！'));
        }

        //添加小程序用户信息到数据库
        $new_data = array(
            'openid' => $res['data']['openid'],
            'unionid' => 0,
            'nickname' => $userInfo['nickName'],
            'headimgurl' => $userInfo['avatarUrl'],
            'sex' => $userInfo['gender'],
            'city' => $userInfo['city'],
            'province' => $userInfo['province'],
            'country' => $userInfo['country'],
            'ctime' => time()
        );
        $add_res = $this->user_lib->addNewUser($new_data);
        if ($add_res['code'] != lang('code_success')) {
            return $this->display->view($displayConfig, dataFormat(lang('code_fail'), '登录失败！'));
        }
        //生成唯一随机串
        $session_key = getRandString(128);
        //将wapp_user_id保存到storage中
        $storage = array(
            's_key' => $session_key,
            's_value' => $add_res['data'],
            'expire_time' => time() + 60 * $this->expire_m,
            'ctime' => time()
        );
        $this->community_lib->addStorage($storage);

        return $this->display->view($displayConfig, dataFormat(lang('code_success'), array('session_key' => $session_key)));
    }

    /**
     * 获取备忘事件
     * @return mixed
     */
    public function getEventList() {
        $displayConfig = array(
            'display' => 'json'
        );
        $wapp_uid = $this->cur_wapp_uid;
        $events = $this->user_lib->getEventMemo($wapp_uid);
        $list = array();
        if ($events['code'] == lang('code_success')) {
            $list = $events['data'];
            foreach ($list as &$v) {
                $v['event_time'] = date('Y-m-d H:i', $v['event_time']);
            }
            unset($v);
        }
        $this->display->view($displayConfig, $list);
    }

    /**
     * 发表足迹
     * @return mixed
     */
    public function addCommunity() {
        $displayConfig = array(
            'display' => 'json'
        );
        $wapp_uid = $this->cur_wapp_uid;
        $post = $this->input->post();
        if (!$wapp_uid) {
            return $this->display->view($displayConfig, dataFormat(lang('code_login_timeout'), '登录超时！'));
        }
        //收集表单formId
        $form_id = $post['form_id'];
        if (substr($form_id, 0, 3) != 'the') {
            $add_data = array(
                'user_id' => $wapp_uid,
                'form_id' => $form_id,
                'ctime' => time(),
                'utime' => 0,
            );
            $this->user_lib->addFormData($add_data);
        }
        //添加足迹
        if (trim($post['content'])) {
            $add_data = array(
                'content' => $post['content'],
//                'image_id' => $post['image_id'],
//                'record_id' => $post['record_id'],
                'type' => $post['type'] == 'true' ? 1 : 0, //1-公开，0-个人
                'user_id' => $wapp_uid,
                'ctime' => time()
            );
            $res = $this->community_lib->addNewCommunity($add_data);
        } else {
            $res = dataFormat(lang('code_fail'), '发表的内容不能为空！');
        }
        return $this->display->view($displayConfig, $res);
    }

    /*
     * 图片上传
     */
    public function upLoad() {
        $displayConfig = array(
            'display' => 'json'
        );
        $this->load->library(array('upload'));
        $post = $this->input->post(); //其他数据处理

        $dir = date('Ymd');
        $upload_path = 'uploads/yxy';

        $uploadConfig = array(
            'upload_path' => $upload_path, // 文件上传的位置，必须是可写的，可以是相对路径或绝对路径
            'allowed_types' => 'gif|jpg|png', // 允许上的文件 MIME 类型，通常文件的后缀名可作为 MIME 类型 可以是数组，也可以是以管道符（|）分割的字符串
            'file_name' => uniqid(), // 如果设置了，CodeIgniter 将会使用该参数重命名上传的文件,设置的文件名后缀必须也要是允许的文件类型 如果没有设置后缀，将使用原文件的后缀名
            'file_ext_tolower' => true, // 如果设置为 TRUE ，文件后缀名将转换为小写
            'overwrite' => false, // 如果设置为 TRUE ，上传的文件如果和已有的文件同名，将会覆盖已存在文件 如果设置为 FALSE ，将会在文件名后加上一个数字
            'max_size' => 65536, // 允许上传文件大小的最大值（单位 KB），设置为 0 表示无限制 注意：大多数 PHP 会有它们自己的限制值，定义在 php.ini 文件中 通常是默认的 2 MB （2048 KB）
            'max_width' => 4096, // 图片的最大宽度（单位为像素），设置为 0 表示无限制
            'max_height' => 2160, // 图片的最大高度（单位为像素），设置为 0 表示无限制
            'min_width' => 0, // 图片的最小宽度（单位为像素），设置为 0 表示无限制
            'min_height' => 0, // 图片的最小高度（单位为像素），设置为 0 表示无限制
            'max_filename' => 0, // 文件名的最大长度，设置为 0 表示无限制
            'max_filename_increment' => 100, // 当 overwrite 参数设置为 FALSE 时，将会在同名文件的后面加上一个自增的数字 这个参数用于设置这个数字的最大值
            'encrypt_name' => false, // 如果设置为 TRUE ，文件名将会转换为一个随机的字符串 如果你不希望上传文件的人知道保存后的文件名，这个参数会很有用
            'remove_spaces' => true, // 如果设置为 TRUE ，文件名中的所有空格将转换为下划线，推荐这样做
            'detect_mime' => true, // 如果设置为 TRUE ，将会在服务端对文件类型进行检测，可以预防代码注入攻击 除非不得已，请不要禁用该选项，这将导致安全风险
            'mod_mime_fix' => true              // 如果设置为 TRUE ，那么带有多个后缀名的文件将会添加一个下划线后缀 这样可以避免触发 Apache mod_mime 。 如果你的上传目录是公开的，请不要关闭该选项，这将导致安全风险
        );

        $this->upload->initialize($uploadConfig);

        if ($this->upload->do_upload('file')) { // 上传成功
            $uploadInfo = $this->upload->data();
            $uploadInfo['file_path'] = $upload_path . '/' . $uploadInfo['file_name'];
            unset($uploadInfo['image_size_str']);

            $file_data = array(
                'file_name' => $uploadInfo['file_name'],
                'file_path' => $uploadInfo['file_path'],
                'file_size' => $uploadInfo['file_size'],
                'image_type' => $uploadInfo['image_type'],
                'resource_type' => $post['res_type'],
                'ctime' => time(),
            );
            $addRes = $this->community_lib->addNewResource($file_data);

            if (lang('code_success') == $addRes['code']) {
                //上传成功时,将img_id更新到发表的足迹中
                $add_data = array(
                    'community_id' => $post['com_id'],
                    'upload_id' => $addRes['data'],
                    'type' => 1,
                    'ctime' => time()
                );
                $this->community_lib->addComImg($add_data);

                $result = array(
                    'code' => lang('code_success'),
                    'id' => $addRes['data'],
                    'path' => $uploadInfo['file_path']
                );
                return $this->display->view($displayConfig, $result);
            } else {
                $result = array(
                    'code' => lang('code_fail'),
                    'msg' => '添加失败',
                );
                return $this->display->view($displayConfig, $result);
            }
        } else {    // 上传失败
            $result = array(
                'code' => lang('code_fail'),
                'msg' => '上传失败',
            );
            return $this->display->view($displayConfig, $result);
        }
    }

    /**
     * 获取足迹列表
     * @return mixed
     */
    public function getComList($type = 0, $page_num = 1, $page_size = 5) {
        $displayConfig = array(
            'display' => 'json'
        );
        $wapp_uid = $this->cur_wapp_uid;

        $com = $this->community_lib->getComList($wapp_uid, $type, $page_num, $page_size);
        $this->display->view($displayConfig, $com);
    }

    /**
     * 获取足迹详情
     * @return mixed
     */
    public function getComDetail($com_id) {
        $displayConfig = array(
            'display' => 'json'
        );

        $com = $this->community_lib->getComDetail($com_id);
        $this->display->view($displayConfig, $com);
    }

    /*
     * 个人中心
     */
    public function getUserDetail() {
        $displayConfig = array(
            'display' => 'json'
        );

        $wapp_uid = $this->cur_wapp_uid;
        $details = $this->user_lib->getUserDetail($wapp_uid);
        $this->display->view($displayConfig, $details);
    }

    /*
     * 消息推送配置验证
     */
    private  function checkToken($signature,$timestamp,$nonce) {
        $token = 'lyxysslq';
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $topStr = sha1($tmpStr);

        if ($topStr == $signature) {
            return true;
        } else {
            return false;
        }
    }
    
    /*
     * 验证
     */
    public function verify() {
        $get = $this->input->get();
        $signature = $get["signature"];
        $timestamp = $get["timestamp"];
        $nonce = $get["nonce"];
        $echostr = $get["echostr"];
        
        $res = $this->checkToken($signature,$timestamp,$nonce);
        if($res){
            echo $echostr;
        }else{
            echo false;
        }
    }






















    public function index() {
        $displayConfig = array(
            'display' => 'json'
        );

        if (!$this->isMobile) {
            echo '非移动端';
            exit;
        }

        $list = $this->community_lib->getConditionDetail(1);
        print_r($list);
        die;
        $this->display->view($displayConfig, $data);
    }
    
    //消息推送测试
    public function pushMsg() {
        $wapp_user_id = 1;
        // $list = $this->community_lib->getConditionDetail(1);
        // $pages = 'pages/detail/detail?com_id='.$list['data']['community_id'];
		$list = "这是一段测试的文字，看看就好~";
		$pages = "pages/detail/detail?com_id=1";
        
        return $this->wapp_push_lib->commentRemind($wapp_user_id,$list,$pages);
    }

}

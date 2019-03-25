<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
 *   小程序用户逻辑层
 *   Created by Netbeans
 *   Author leslie <390940063@qq.com>
 *   Date 2018-03-30 14:13
 * */

class User_lib {
    
    /*
     * 文件类型--足迹
     */
    const RESOURCE_TYPE_FOOT = 1;
    /*
     * 文件类型--宝贝相片
     */
    const RESOURCE_TYPE_PHOTO = 2;
    /*
     * 状态--正常
     */
    const STATUS_NORMAL = 0;
    /*
     * 状态--删除
     */
    const STATUS_DEL = 1;
    /*
     * 成长变化值类型--身高
     */
    const GROUP_TYPE_HEIGHT = 1;
    /*
     * 成长变化值类型--体重
     */
    const GROUP_TYPE_WEIGHT = 2;
    /*
     * 备忘事件类型--宝贝出生日
     */
    const EVENT_TYPE_BIRTHDAY = 1;

    /**
     * CI超级对象
     * @var object
     */
    private $CI;

    /**
     * 构造方法,获取CI超级对象
     */
    public function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->model(array('user/app_user_model', 'user/user_form_model', 'user/user_baby_model', 'user/baby_group_model', 'event/event_memo_model', 'event/config_model'));
    }

    /**
     * 获取小程序登录用户信息
     * @param $code
     * @return array
     */
    public function getUserSession($code) {
        $this->CI->load->library('utils/http');
        $this->CI->http->setHost('https://api.weixin.qq.com/sns/jscode2session');
        $data = array(
            'appid' => WAPP_ID_LYX,
            'secret' => WAPP_SECRET_LYX,
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        );
        $res = $this->CI->http->post($data);

        if (!empty($res)) {
            $res = json_decode($res, true);
            if (isset($res['errcode'])) {
                return dataFormat(lang('code_fail'), "{$res['errcode']} : {$res['errmsg']}");
            } else {
                return dataFormat(lang('code_success'), $res);
            }
        }

        return dataFormat(lang('code_fail'), "获取用户信息失败：{$res}");
    }

    /**
     * 添加系统配置
     * @param   data
     * @param
     * @param
     * @return
     * */
    public function addConfigData($data) {
        $res = $this->CI->config_model->add($data);
        if ($res) {
            return dataFormat(lang('code_success'), '操作成功');
        } else {
            return dataFormat(lang('code_fail'), '操作失败');
        }
    }

    /**
     * 根据key获取系统配置
     * @param   key
     * @param
     * @param
     * @return
     * */
    public function getConfigValuesByKey($key) {
        $params = array(
            'select' => 'values',
            'where' => array(
                'key' => $key,
            ),
            'order' => array(
                'ctime' => 'desc'
            ),
            'pages' => array(
                'offset' => 0,
                'limit' => 1
            )
        );
        $res = current($this->CI->config_model->search($params));
        if (!empty($res)) {
            return dataFormat(lang('code_success'), json_decode(current($res), TRUE));
        } else {
            return dataFormat(lang('code_fail'), '配置不存在');
        }
    }

    /**
     * 根据key更新系统配置
     * @param   key
     * @param
     * @param
     * @return
     * */
    public function updateConfigValuesByKey($key, $data) {
        $res = $this->CI->config_model->update(array('key' => $key), $data);
        if ($res) {
            return dataFormat(lang('code_success'), '操作成功');
        } else {
            return dataFormat(lang('code_fail'), '操作失败');
        }
    }

    /**
     * 添加用户form_id
     * @param   data
     * @param
     * @param
     * @return
     * */
    public function addFormData($data) {
        $res = $this->CI->user_form_model->add($data);
        if ($res) {
            return dataFormat(lang('code_success'), '操作成功');
        } else {
            return dataFormat(lang('code_fail'), '操作失败');
        }
    }

    /**
     * 获取用户有效form_id
     * @param   user_id
     * @param
     * @param
     * @return
     * */
    public function getFormID($user_id) {
        $limit_time = time() - 7 * 86400; //form_id有效时间为7天
        $params = array(
            'select' => 'id,form_id',
            'where' => array(
                'user_id' => $user_id,
                'utime' => 0,
                'ctime >' => $limit_time
            ),
            'order' => array(
                'ctime' => 'asc'
            ),
            'pages' => array(
                'offset' => 0,
                'limit' => 1
            )
        );
        $res = current($this->CI->user_form_model->search($params));
        if (!empty($res)) {
            return dataFormat(lang('code_success'), $res);
        } else {
            return dataFormat(lang('code_fail'), '没有可用的form_id');
        }
    }

    /**
     * 更新已用form_id时间
     * @param   id
     * @param
     * @param
     * @return
     * */
    public function updateFormID($id) {
        $res = $this->CI->user_form_model->update(array('id' => $id), array('utime' => time()));
        if ($res) {
            return dataFormat(lang('code_success'), '操作成功');
        } else {
            return dataFormat(lang('code_fail'), '操作失败');
        }
    }

    /**
     * 根据openid获取用户基本信息
     * @param
     * @param
     * @param
     * @return
     * */
    public function getUserByOpenId($openid) {
        $res = $this->CI->app_user_model->search(array('where' => array('openid' => $openid)));
        if ($res) {
            return dataFormat(lang('code_success'), current($res));
        } else {
            return dataFormat(lang('code_fail'), '没有用户相关信息');
        }
    }

    /**
     * 添加用户基本信息
     * @param
     * @param
     * @param
     * @return
     * */
    public function addNewUser($data) {
        $user = $this->getUserByOpenId($data['openid']);
        if ($user['code'] == lang('code_success')) {
            return dataFormat(lang('code_success'), $user['data']['user_id']);
        } else {
            $id = $this->CI->app_user_model->add($data);
            if ($id) {
                return dataFormat(lang('code_success'), $id);
            } else {
                return dataFormat(lang('code_fail'), '操作失败');
            }
        }
    }
    
    /* 获取用户信息
     * @param   user_id
     * @param
     * @param
     * @return
     * * */
    public function getUserInfo($user_id) {
        $users = current($this->CI->app_user_model->search(array('where' => array('user_id' => $user_id))));
        if(!empty($users)){
            return dataFormat(lang('code_success'), $users);
        }else{
            return dataFormat(lang('code_fail'), '没有找到用户相关信息');
        }
    }


    /* 获取用户详细信息
     * @param   user_id
     * @param
     * @param
     * @return
     * * */
    public function getUserDetail($user_id) {
        $params = array(
            'select' => 'nickname,sex,headimgurl',
            'where' => array(
                'user_id' => $user_id,
            ),
        );
        $users = current($this->CI->app_user_model->search($params));
        $baby_list = $this->CI->user_baby_model->search(array('where' => array('user_id' => $user_id)));
        if(!empty($baby_list)){
            $this->CI->load->model(array('upload/upload_model'));
            $baby_imgs = array();
            foreach($baby_list as &$val){
                $val['bathday'] = date('Y年m月d日', $val['bathday']);
                $params = array(
                    'select' => 'file_path',
                    'where' => array(
                        'id' => $val['imgurl_id'],
                        'status' => self::STATUS_NORMAL,
                        'resource_type' => self::RESOURCE_TYPE_PHOTO
                    )
                );
                $file_path = current($this->CI->upload_model->search($params));
                $img_url = !empty($file_path) ? $file_path['file_path'] : '';
                $val['img_url'] = WEB_HOST.$img_url;
                $baby_imgs[] = WEB_HOST.$img_url;
            }
            unset($val);
        }
        $users['baby_list'] = $baby_list;
        $users['baby_imgs'] = $baby_imgs;
        return $users;
    }

    /**
     * 获取用户备忘事件
     * @param   user_id
     * @param   object_id
     * @param   object_type
     * @return
     * */
    public function getEventMemo($user_id, $object_id = 0, $object_type = 0) {
        $params = array(
            'select' => '*',
            'where' => array(
                'user_id' => $user_id,
                'status' => self::STATUS_NORMAL
            ),
            'order' => array(
                'ctime' => 'asc'
            )
        );
        if ($object_id) {
            $params['where']['object_id'] = $object_id;
        }
        if ($object_type) {
            $params['where']['object_type'] = $object_type;
        }
        $res = $this->CI->event_memo_model->search($params);
        if (!empty($res)) {
            return dataFormat(lang('code_success'), $res);
        } else {
            return dataFormat(lang('code_fail'), '没有相关事件');
        }
    }

    /**
     * 添加备忘事件
     * @param   data
     * @param
     * @param
     * @return
     * */
    public function addEventMemo($data) {
        $id = $this->CI->event_memo_model->add($data);
        if ($id) {
            return dataFormat(lang('code_success'), $id);
        } else {
            return dataFormat(lang('code_fail'), '操作失败');
        }
    }

    /**
     * 编辑备忘事件
     * @param   id
     * @param   data
     * @param
     * @return
     * */
    public function editEventMemo($id, $data) {
        //先查询该备忘事件是否存在
        $event = $this->CI->event_memo_model->search(array('id' => $id), array('status' => self::STATUS_NORMAL));
        if (empty($event)) {
            return dataFormat(lang('code_fail'), '事件不存在');
        }
        $res = $this->CI->event_memo_model->update(array('id' => $id), $data);
        if ($res) {
            return dataFormat(lang('code_success'), '操作成功');
        } else {
            return dataFormat(lang('code_fail'), '操作失败');
        }
    }

    /**
     * 添加宝贝详细信息
     * @param   data
     * @param   
     * @param
     * @return
     * */
    public function addBabyDetail($data) {
        $baby_id = $this->CI->user_baby_model->add($data);
        if ($baby_id) {
            //默认生成宝贝生日事件
            $event_data = array(
                'event_name' => '今天是宝贝 ' . $data['name'] . '的出生日',
                'event_time' => $data['bathday'],
                'user_id' => $data['user_id'],
                'object_type' => self::EVENT_TYPE_BIRTHDAY,
                'object_id' => $baby_id,
                'ctime' => time(),
            );
            $this->CI->event_memo_model->add($event_data);
            //添加成长数据
            $group_data = array(
                'baby_id' => $baby_id,
                'type' => self::GROUP_TYPE_HEIGHT,
                'change' => $data['height'],
                'ctime' => time(),
            );
            $this->CI->baby_group_model->add($group_data);
            $group_data = array(
                'baby_id' => $baby_id,
                'type' => self::GROUP_TYPE_WEIGHT,
                'change' => $data['weight'],
                'ctime' => time(),
            );
            $this->CI->baby_group_model->add($group_data);
            return dataFormat(lang('code_success'), $val);
        } else {
            return dataFormat(lang('code_fail'), '操作失败');
        }
    }

    /**
     * 获取用户宝贝详细信息
     * @param   user_id
     * @param
     * @param
     * @return
     * */
    public function getUserBaby($user_id) {
        $params = array(
            'select' => '*',
            'where' => array(
                'user_id' => $user_id,
            ),
        );
        $res = $this->CI->user_baby_model->search($params);
        if (!empty($res)) {
            return dataFormat(lang('code_success'), $res);
        } else {
            return dataFormat(lang('code_fail'), '没有相关信息');
        }
    }

    /**
     * 编辑宝贝详细信息
     * @param   baby_id
     * @param   user_id
     * @param   data
     * @return
     * */
    public function editBabyInfo($baby_id, $user_id, $data) {
        $res = $this->CI->user_baby_model->update(array('baby_id' => $baby_id), $data);
        if ($res) {
            //如果修改了生日信息，则要修改对应事件
            if (isset($data['bathday'])) {
                $event = $this->getEventMemo($user_id, $baby_id, self::EVENT_TYPE_BIRTHDAY);
                $this->editEventMemo($event[0]['data']['id'], array('event_time' => $data['bathday']));
            }
            //如果修改了身高或体重，则相应记录
            if (isset($data['height'])) {
                $group_data = array(
                    'baby_id' => $baby_id,
                    'type' => self::GROUP_TYPE_HEIGHT,
                    'change' => $data['height'],
                    'ctime' => time(),
                );
                $this->CI->baby_group_model->add($group_data);
            }
            if (isset($data['weight'])) {
                $group_data = array(
                    'baby_id' => $baby_id,
                    'type' => self::GROUP_TYPE_WEIGHT,
                    'change' => $data['weight'],
                    'ctime' => time(),
                );
                $this->CI->baby_group_model->add($group_data);
            }
            return dataFormat(lang('code_success'), '操作成功');
        } else {
            return dataFormat(lang('code_fail'), '操作失败');
        }
    }

}

<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
 *   小程序足迹逻辑层
 *   Created by Netbeans
 *   Author leslie <390940063@qq.com>
 *   Date 2018-04-01 14:13
 **/
class Community_lib{

    /*
     * 状态--正常
     */
    const STATUS_NORMAL = 0;
    /*
     * 状态--删除
     */
    const STATUS_DEL = 1;
    
    /*
     * 社区情况--评论
     */
    const CONDITION_TYPE_COMMENT = 1;
    /*
     * 社区情况--点赞
     */
    const CONDITION_TYPE_PRAISE = 2;
    

    /**
     * CI超级对象
     * @var object
     */
    private $CI;

    /**
     * 构造方法,获取CI超级对象
     */
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model(array('community/community_model','community/condition_model','upload/upload_model','event/storage_model','community/com_images_model'));
        $this->CI->load->library(array('applet/user_lib'));
    }
    
    /**
     * 添加资源
     * @param   data
     * @param
     * @param
     * @return
     **/
    public function addNewResource($data) {
        $upload_id = $this->CI->upload_model->add($data);
        if ($upload_id) {
            return dataFormat(lang('code_success'), $upload_id);
        } else {
            return dataFormat(lang('code_fail'), '操作失败');
        }
    }
    
    /**
     * 添加资源内容关联
     * @param   data
     * @param
     * @param
     * @return
     **/
    public function addComImg($data) {
        $res = $this->CI->com_images_model->add($data);
        if ($res) {
            return dataFormat(lang('code_success'), $res);
        } else {
            return dataFormat(lang('code_fail'), '操作失败');
        }
    }
    
    /**
     * 发表足迹
     * @param
     * @param
     * @param
     * @return
     **/
    public function addNewCommunity($data) {
        $res = $this->CI->community_model->add($data);
        if ($res) {
            return dataFormat(lang('code_success'), $res);
        } else {
            return dataFormat(lang('code_fail'), '操作失败');
        }
    }
    
    /**
     * 编辑足迹
     * @param   id
     * @param   data
     * @param
     * @return
     **/
    public function editCommunity($id,$data) {
        $res = $this->CI->community_model->update(array('id' => $id),$data);
        if ($res) {
            return dataFormat(lang('code_success'), '操作成功');
        } else {
            return dataFormat(lang('code_fail'), '操作失败');
        }
    }
    
    /**
     * 添加社区情况
     * @param   data
     * @param
     * @param
     * @return
     **/
    public function addCondition($data) {
        //判断是否为赞
        if($data['type'] == 2){
            $where = array(
                'community_id' => $data['community_id'],
                'type' => $data['type'],
                'from_user_id' => $data['from_user_id'],
            );
            $condition = $this->getComCondition($where);
            if($condition['code'] == lang('code_success')){
                $status = $condition['data'][0]['status'] ? 0 : 1;
                $this->editCondition(array('id' => $condition['data'][0]['id']), array('status' => $status));
                return dataFormat(lang('code_success'), $condition['data'][0]['id']);
            }
        }
        $id = $this->CI->condition_model->add($data);
        if ($id) {
            return dataFormat(lang('code_success'), $id);
        } else {
            return dataFormat(lang('code_fail'), '操作失败');
        }
    }
    
    /**
     * 获取社区情况
     * @param   where
     * @param   
     * @param
     * @return
     **/
    public function getComCondition($where) {
        $res = $this->CI->condition_model->search($where);
        if ($res) {
            return dataFormat(lang('code_success'), $res);
        } else {
            return dataFormat(lang('code_fail'), '没有找到相关信息');
        }
    }
    
    /* 获取内容相关资源
     * @param   community_id
     * @param   
     * @param
     * @return
     * * */
    public function getContentImg($community_id) {
        $this->CI->com_images_model->setAlias('p1');
        $params = array(
            'select' => 'p1.type,p2.file_path',
            'joins' => array(
                array(
                    'join_table' => 'upload as p2',
                    'join_where' => 'p1.upload_id = p2.id',
                    'join_type'  => 'left'
                ),
            ),
            'where' => array(
                'p1.community_id' => $community_id,
                'p2.status' => self::STATUS_NORMAL
            ),
            'order' => array(
                'p1.ctime' => 'desc'
            )
        );
        $list = $this->CI->com_images_model->search($params);
        $this->CI->com_images_model->clearAlias();
        if(!empty($list)){
            foreach($list as &$val){
                $val['file_path'] = WEB_HOST.$val['file_path'];
            }
            unset($val);
        }
        return $list;
    }


    /* 获取社区情况列表
     * @param   type    0-个人，1-公开
     * @param   user_id
     * @param
     * @return
     * * */
    public function getComList($user_id,$type = 0,$page_num = 1,$page_size = 5) {
        $this->CI->community_model->setAlias('p1');
        $params = array(
            'select' => 'p1.id,p1.content,p1.type,p1.user_id,FROM_UNIXTIME(p1.ctime,"%Y-%m-%d %H:%i") as cdate,p2.nickname,p2.sex,p2.headimgurl',
            'joins' => array(
                array(
                    'join_table' => 'app_user as p2',
                    'join_where' => 'p1.user_id = p2.user_id',
                    'join_type'  => 'left'
                ),
            ),
            'order' => array(
                'p1.ctime' => 'desc'
            ),
            'pages' => array(
                'offset' => ($page_num - 1)*$page_size,
                'limit' => $page_size
            )
        );
        if($type){
            $params['where'] = array(
                'p1.status' => self::STATUS_NORMAL,
                'p1.type' => $type
            );
        }else{
            $params['where'] = array(
                'p1.status' => self::STATUS_NORMAL,
                'p1.type' => $type,
                'p1.user_id' => $user_id
            );
        }
        $com_list = $this->CI->community_model->search($params);
        $this->CI->community_model->clearAlias();
        foreach($com_list as &$val){
            $val['img_list'] = $this->getContentImg($val['id']);
        }
        unset($val);
        return $com_list;
    }
    
    /* 获取社区内容详情
     * @param   com_id
     * @param
     * @param
     * @return
     * * */
    public function getComDetail($com_id) {
        $com = current($this->CI->community_model->search(array('where' => array('id' => $com_id))));
        if(!empty($com)){
            $com['cdate'] = date('Y-m-d H:i',$com['ctime']);
            $com['img_list'] = $this->getContentImg($com_id);
            $img_file_list = array_column($this->getContentImg($com_id), 'file_path');
            $com['img_file_list'] = $img_file_list;
            $com['user_detail'] = $this->CI->user_lib->getUserDetail($com['user_id']);
            //评论
            $param_c = array(
                'select' => 'id,moment,from_user_id,to_user_id',
                'where' => array(
                    'community_id' => $com_id,
                    'type' => self::CONDITION_TYPE_COMMENT,
                    'status' => self::STATUS_NORMAL
                )
            );
            $comment = $this->CI->condition_model->search($param_c);
            $com['comment_list'] = $comment;
            
            //点赞数
            $param_p = array(
                'select' => 'count(*) as cnt',
                'where' => array(
                    'community_id' => $com_id,
                    'type' => self::CONDITION_TYPE_PRAISE,
                    'status' => self::STATUS_NORMAL
                )
            );
            $priase = $this->CI->condition_model->search($param_p);
            $com['priase_num'] = $priase[0]['cnt'];
            
            return dataFormat(lang('code_success'), $com);
        }else{
            return dataFormat(lang('code_fail'), '该内容不存在');
        }
    }
    
    /* 获取评论内容详情
     * @param   condition_id
     * @param
     * @param
     * @return
     * * */
    public function getConditionDetail($condition_id) {
        $this->CI->condition_model->setAlias('p1');
        $params = array(
            'select' => 'p1.community_id,p1.moment,p1.from_user_id,p1.ctime,p2.nickname,p3.content',
            'joins' => array(
                array(
                    'join_table' => 'app_user as p2',
                    'join_where' => 'p1.from_user_id = p2.user_id',
                    'join_type'  => 'left'
                ),
                array(
                    'join_table' => 'community as p3',
                    'join_where' => 'p1.community_id = p3.id',
                    'join_type'  => 'left'
                ),
            ),
            'where' => array(
                'p1.id' => $condition_id,
                'p1.status' => self::STATUS_NORMAL,
                'p3.status' => self::STATUS_NORMAL
            ),
        );
        $res = current($this->CI->condition_model->search($params));
        $this->CI->condition_model->clearAlias();
        if(!empty($res)){
            return dataFormat(lang('code_success'), $res);
        }else{
            return dataFormat(lang('code_fail'), '没有找到相关评论内容');
        }
    }


    /**
     * 编辑社区情况
     * @param   where
     * @param   data
     * @param
     * @return
     **/
    public function editCondition($where,$data) {
        //判断该情况是否存在
        $condition = $this->getComCondition(array('id' => $where['id']));
        if($condition['code'] == lang('code_fail')){
            return dataFormat(lang('code_fail'), '没有该情况信息');
        }
        $res = $this->CI->condition_model->update($where,$data);
        if ($res) {
            return dataFormat(lang('code_success'), '操作成功');
        } else {
            return dataFormat(lang('code_fail'), '操作失败');
        }
    }
    
    /**
     * 添加存储
     * @param   data
     * @param
     * @param
     * @return
     **/
    public function addStorage($data) {
        $id = $this->CI->storage_model->add($data);
        if ($id) {
            return dataFormat(lang('code_success'), $id);
        } else {
            return dataFormat(lang('code_fail'), '操作失败');
        }
    }
    
    /**
     * 获取存储值
     * @param   key
     * @param   
     * @param
     * @return
     **/
    public function getStorageByKey($key) {
        $params = array(
            'select' => 'id,s_key,s_value',
            'where' => array(
                's_key' => "$key",
                'expire_time >' => time()
            ),
        );
        $res = current($this->CI->storage_model->search($params));
        if (!empty($res)) {
            return dataFormat(lang('code_success'), $res);
        } else {
            return dataFormat(lang('code_fail'), '没有找到相关信息');
        }
    }
    
    /**
     * 编辑存储值
     * @param   where
     * @param   data
     * @param
     * @return
     **/
    public function editStorage($where,$data) {
        //判断该情况是否存在
        $storage = $this->getStorageByKey($where['s_key']);
        if($storage['code'] == lang('code_fail')){
            return dataFormat(lang('code_fail'), '没有该情况信息');
        }
        $res = $this->CI->storage_model->update($where,$data);
        if ($res) {
            return dataFormat(lang('code_success'), '操作成功');
        } else {
            return dataFormat(lang('code_fail'), '操作失败');
        }
    }
    
}
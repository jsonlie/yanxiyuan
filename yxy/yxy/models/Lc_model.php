<?php

/**
 * ription 基础模型
 *
 * @author cat <lynxcatdeng@gmail.com>
 *         @create 2015-03-18
 */
class Lc_model extends CI_Model {

    protected $table_name = '';
    
    public static $gl_db_master = FALSE;
    
    protected $alias      = FALSE;
    
    function __construct() {
        parent::__construct();

        $this->initDBConnect();
    }

    /**
     * 初始化数据库链接
     */
    public function initDBConnect(){
        if(!isset(self::$gl_db_master) || empty(self::$gl_db_master) || empty(self::$gl_db_master->conn_id)){
            self::$gl_db_master = $this->load->database('master', true);
        }
		
    }

    public function getLastSql() {
        return self::$gl_db_master->last_query();
    }

    /**
     * 设置默认表名
     *
     * @param string $table_name 表名
     */
    public function setTableName($table_name) {
        $this->table_name = $table_name;
    }

    /**
     * 设置别名
     *
     * @param string $alias 别名
     */
    public function setAlias($alias) {
        if (!$this->alias) { // 防止重复设置别名
            $this->table_name .= ' as ' . $alias;
            $this->alias = TRUE;
        }
    }

    /**
     * 清除别名
     */
    public function clearAlias() {
        $temp             = explode(' as ', $this->table_name);
        $this->table_name = $temp[0];

        $this->alias = false;
    }

    /**
     * 编辑用户信息
     *
     * @param Number $admin_id 用户ID
     * @param Array $param 需要改变的项
     */
    public function update($where, $params) {
        self::$gl_db_master->where($where);
        return self::$gl_db_master->update($this->table_name, $params);
    }

    /**
     * 添加角色
     *
     * @param Array $params 角色信息
     */
    public function add($params) {
        self::$gl_db_master->insert($this->table_name, $params);
        return self::$gl_db_master->insert_id();
    }

    /**
     * 批量插入数据
     *
     * @param Array $params 角色信息
     */
    public function addBatch(array $addData) {
        return self::$gl_db_master->insert_batch($this->table_name, $addData);
    }

    /**
     * 通过条件获得总条数
     */
    public function getCount($params) {
        self::$gl_db_master->from($this->table_name);

        if (isset($params['where']) && !empty($params['where'])) {
            // 处理where in
            if (isset($params['where']['in']) && !empty($params['where']['in'])) {
                foreach ($params['where']['in'] as $key => $value) {
                    self::$gl_db_master->where_in($key, $value);
                }
                unset($params['where']['in']);
            }
            // TOTO 剔除掉不能查询的where
            self::$gl_db_master->where($params['where']);
        }

        if (isset($params['not_in']) && !empty($params['not_in'])) {
            foreach ($params['not_in'] as $key => $value) {
                self::$gl_db_master->where_not_in($key, $value);
            }
            unset($params['not_in']);
        }

        // 设置jion语句
        if (isset($params['joins']) && !empty($params['joins'])) {

            if (isset($params['joins'][0]) && is_array($params['joins'][0])) {
                foreach ($params['joins'] as $key => $value) {
                    self::$gl_db_master->join($value['join_table'], $value['join_where'], $value['join_type']);
                }
            } else {
                self::$gl_db_master->join($params['joins']['join_table'], $params['joins']['join_where'], $params['joins']['join_type']);
            }
        }

        if (isset($params['group']) && !empty($params['group'])) {
            // TODO 剔除不能groupby的字段
            self::$gl_db_master->group_by($params['group']);
        }

        return self::$gl_db_master->count_all_results();
    }

    /**
     * 搜索用户数据
     */
    public function search($params = array()) {
        self::$gl_db_master->from($this->table_name);

        if (isset($params['select']) && !empty($params['select'])) {
            self::$gl_db_master->select($params['select']);
        }

        if (isset($params['where']) && !empty($params['where'])) {

            // 处理where in
            if (isset($params['where']['in']) && !empty($params['where']['in'])) {
                foreach ($params['where']['in'] as $key => $value) {
                    self::$gl_db_master->where_in($key, $value);
                }
                unset($params['where']['in']);
            }
            // TOTO 剔除掉不能查询的where
            self::$gl_db_master->where($params['where']);
        }

        if (isset($params['not_in']) && !empty($params['not_in'])) {
            foreach ($params['not_in'] as $key => $value) {
                self::$gl_db_master->where_not_in($key, $value);
            }
            unset($params['not_in']);
        }

        if (isset($params['order']) && !empty($params['order'])) {
            // TOTO 剔除不能排序的字段

            foreach ($params['order'] as $key => $value) {
                self::$gl_db_master->order_by($key, $value);
            }
        }

        if (isset($params['pages']) && !empty($params['pages'])) {
            self::$gl_db_master->limit($params['pages']['limit'], $params['pages']['offset']);
        }

        // 设置jion语句
        if (isset($params['joins']) && !empty($params['joins'])) {
            if (isset($params['joins'][0]) && is_array($params['joins'][0])) {
                foreach ($params['joins'] as $key => $value) {
                    self::$gl_db_master->join($value['join_table'], $value['join_where'], $value['join_type']);
                }
            } else {
                self::$gl_db_master->join($params['joins']['join_table'], $params['joins']['join_where'], $params['joins']['join_type']);
            }
        }

        if (isset($params['group']) && !empty($params['group'])) {
            // TODO 剔除不能groupby的字段
            self::$gl_db_master->group_by($params['group']);
        }

        if (isset($params['having']) && !empty($params['having'])) {
            self::$gl_db_master->having($params['having']);
        }


        $query = self::$gl_db_master->get();

        return $query->result_array();
    }

    /**
     * 删除数据
     *
     * @param Array $where 需要删除的条件
     */
    public function delete($where) {
        self::$gl_db_master->where($where);
        return self::$gl_db_master->delete($this->table_name);
    }

    /**
     * 开启事务
     *
     * @return
     *
     */
    public function trans_begin() {
        self::$gl_db_master->trans_start();
    }

    /**
     * 事务处理状态
     *
     * @return
     *
     */
    public function trans_status() {
        return self::$gl_db_master->trans_status();
    }

    /**
     * 事务提交
     *
     * @return
     *
     */
    public function trans_commit() {
        return self::$gl_db_master->trans_commit();
    }

    /**
     * 事务回滚
     *
     * @return
     *
     */
    public function trans_rollback() {
        $res = self::$gl_db_master->trans_back();
    }

    /**
     * 获取当前嵌套事务层级
     * @return mixed
     */
    public function getTransDepth(){
        return self::$gl_db_master->getTransDepth();
    }

    /**
     * 查询
     *
     * @param $sql 查询语句
     */
    public function query($sql) {
        $query = self::$gl_db_master->query($sql);
        return $query->result_array();
    }

    /**
     * 执行sql
     *
     * @param $sql 查询语句
     */
    public function querys($sql) {
        $query = self::$gl_db_master->query($sql);
        return $query;
    }

}

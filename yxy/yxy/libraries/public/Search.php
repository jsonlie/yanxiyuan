<?php
/**
 * @author cat<lynxcatdeng@gmail.com>
 * @create 2015-4-4
 * @description 公用搜索辅助库
 */
class Search {
    private $CI, $where = array(), $not_in = array(), $order = array(), $pages = array(), $search = array(), $joins = array(), $select = array(),$group = '',$having = '' ,$limit = 10, $page = 1, $count = 0, $countpage = 0, $sets = array('where' => 0, 'order' => 0, 'pages' => 0, 'search' => 0, 'joins' => 0, 'select' => 0, 'group' => 0, 'not_in' => 0, 'having' => 0);
    public $default = array();

    public function __construct() {
        $this->CI = &get_instance();
        $this->CI->load->library(array('utils/dataformat.php' => 'format'));
    }


    /**
     * 设置默认值
     */
    public function setDefault($key, $val = ''){
        if(is_string($key)){
            $this->default[$key] = $val;
        }else if(is_array($key)){
            $this->default = $key;
        }
        return $this;
    }

    /**
     * 获得查询条件
     */
    public function getWhere(){
        if($this->sets['where']){
            return $this->where;
        }else{
            $where = array();
            if(isset($this->default['where']) && !empty($this->default['where'])){
                $where = $this->default['where'];
            }

            $query = urldecode($this->CI->input->get('q'));
            if($query != ''){
                $query = json_decode($query,true);
                $keys = array();
                $vals = array();
                if(is_array($query) && count($query)){
                    foreach($query as $key => $value){
                        $key = trim($key);
                        $value = trim($value);

                        $res = authcode(base64_decode($key));
                        if($res == ''){
                            continue;
                        }

                        array_push($this->search, array('key' => $res, 'val' => $value));
                        //改动---2016-8-29 万伟宝
                        $this->search[$res]=$value;

                        $this->sets['search'] = 1;

                        $res = explode("-",$res);
                        $key = $res[0];
                        $symbol = isset($res[1]) ? $res[1] : '';
                        $format = isset($res[2]) ? $res[2] : '';
                        $fun = $format;
                        /**
                         * 数据格式化
                         */
                        if(method_exists($this->CI->format,$fun)){
                            $value = $this->CI->format->$fun($value);
                            if(is_array($value)){
                                $where[$key.' <'] = $value[1];
                                $value = $value[0];
                            }
                        }

                        if($symbol == 'in' || count(explode(",", $value)) > 1){
                            if(!isset($where['in'])){
                                $where['in'] = array();
                            }
                            if(is_array($where)){
                                $where['in'][$key] = explode(",", $value);
                            }
                            else{
                                $where .= " and $key in ($value)";
                            }

                            continue;
                        }else if($symbol == 'like'){
                            $key = $key . ' like';
                            $value = "%" . $value . "%";
                        }else if(in_array($symbol,array('>','<','>=','<=','!='))){
                            $key = $key . ' ' . $symbol;
                        }
                        else{
                            if(!is_array($where)){
                                $key = $key . ' = ';
                            }
                        }

                        if(is_array($where)){
                            $where[$key] = $value;
                        }
                        else{
                            $where .= " and $key '$value'";
                        }
                    }
                }
            }

            $this->sets['where'] = 1;
            $this->where = $where;
            return $this->where;
        }
    }

    /**
     * 获得分页参数
     */
    public function getPages(){
        if($this->sets['pages'] || $this->count == 0){
            return $this->pages;
        }else{
            $page = trim($this->CI->input->get('page'));
            $limit = trim($this->CI->input->get('limit'));
            //TODO 判断参数是否正常
            if($page > 0){
                $this->page = $page;
            }

            if($limit > 0){
                $this->limit = $limit;
            }

            if($this->limit > $this->count){ // 修正当前条数
                $this->limit = $this->count;
            }

            $countpage = ceil($this->count / $this->limit); // 通过总条数算总页数

            $this->countpage = ($countpage > 0) ? $countpage : 1;

            if($this->page > $this->countpage){ // 修正当前页数
                $this->page = $this->countpage;
            }else if($this->page < 1){
                $this->page = 1;
            }

            $offset = ($this->page - 1) * $this->limit;

            $this->pages = array(
                'limit' => $this->limit,
                'offset' => $offset
            );

            $this->sets['pages'] = 1;
            return $this->pages;
        }
    }

    /**
     * 获得排序
     */
    public function getOrder(){
        if($this->sets['order']){
            return $this->order;
        }else{
            $sort = trim($this->CI->input->get('sort'));
            $dir = trim($this->CI->input->get('dir'));
            //TODO 判断是sort否在可以排序白名单中
            if(is_string($sort) && !empty($sort)){
                $dir = $dir ? $dir : 'desc';
                $this->order[$sort] = $dir;
                $this->sets['order'] = 1;
            }else if(isset($this->default['order']) && !empty($this->default['order'])){
                $this->order = $this->default['order'];
                $this->sets['order'] = 1;
            }
            return $this->order;
        }
    }

    /**
     * 获得分组
     */
    public function getGroup(){
        if($this->sets['group']){
            return $this->group;
        }else{
            if(isset($this->default['group']) && !empty($this->default['group'])){
                $this->group = $this->default['group'];
                $this->sets['group'] = 1;
            }
            return $this->group;
        }
    }

    /**
     * 获得having --万伟宝2017/2/9
     */
    public function getHaving(){
        if($this->sets['having']){
            return $this->having;
        }else{
            if(isset($this->default['having']) && !empty($this->default['having'])){
                $this->having = $this->default['having'];
                $this->sets['having'] = 1;
            }
            return $this->having;
        }
    }

    /**
     * 获得搜索项
     */
    public function getSearch(){
        if(!$this->sets['search'] && isset($this->default['search'])){
            $this->search = $this->default['search'];
        }
        return $this->search;
    }

    /**
     * 获得搜索项
     */
    public function getJoins(){
        if(!$this->sets['joins'] && isset($this->default['joins'])){
            $this->joins = $this->default['joins'];
        }
        return $this->joins;
    }

    /**
     * 获得查询列
     */
    public function getSelect(){
        if (! $this->sets['select'] && isset($this->default['select'])) {
            $this->select = $this->default['select'];
        }

        return $this->select;
    }

    /**
     * 获得查询列
     */
    public function getNot_in(){
        if (! $this->sets['not_in'] && isset($this->default['not_in'])) {
            $this->not_in = $this->default['not_in'];
        }

        return $this->not_in;
    }

    /**
     * 获得完整的查询参数
     */
    public function getParams(){
        $data = array();

        $funs = array('where', 'pages', 'order', 'search', 'joins', 'select', 'group', 'not_in', 'having');

        foreach ($funs as $fun) {
            $data[$fun] = $this->{'get'.$fun}();
        }

        return $data;
    }

    /**
     * 设置数据总条数,用于分页
     * @param int $limit 条数 为0代表全部
     */
    public function setCountLimit($limit){
        $this->count = $limit;
        return $this;
    }

    /**
     * 设置每页条数
     */
    public function setLimit($limit){
        $this->limit = $limit;
        return $this;
    }

    /**
     * 设置页数
     */
    public function setPage($page){
        $this->page = $page;
        return $this;
    }

    /**
     * 获得分页工具条
     */
    public function getPaginate($quickjump = false){
        $page = $this->page;
        $countpage = $this->countpage;
        $paginate = '';

        if($this->count != 0){
            $paginate .= '<div class="fg-toolbar ui-toolbar ui-widget-header ui-corner-bl ui-corner-br ui-helper-clearfix">';
            $paginate .= '<div class="dataTables_paginate fg-buttonset ui-buttonset fg-buttonset-multi ui-buttonset-multi paging_full_numbers">';

            if($page == 1){
                $paginate .= '<a class="first ui-corner-tl ui-corner-bl fg-button ui-button ui-state-default ui-state-disabled">首页</a>';
                $paginate .= '<a class="previous fg-button ui-button ui-state-default ui-state-disabled" id="DataTables_Table_0_previous">上一页</a>';
            }else{
                $paginate .= '<a class="first ui-corner-tl ui-corner-bl fg-button ui-button ui-state-default">首页</a>';
                $paginate .= '<a class="previous fg-button ui-button ui-state-default" id="DataTables_Table_0_previous">上一页</a>';
            }

            $paginate .= '<span>';
            if($countpage <= 5){
                $i = 1;
            }else{
                if($countpage - $page < 5){
                    $i = $countpage - 4;
                }else if($countpage - $page == 5){
                    $i = $page;
                }else if($countpage - $page > 5){
                    $i = $page;
                    $countpage = $page + 4;
                }
            }

            for(;$i <= $countpage;$i++){
                if($i == $page){
                    $paginate .= '<a class="fg-button ui-button ui-state-default ui-state-disabled">' . $i . '</a>';
                }else{
                    $paginate .= '<a class="fg-button ui-button ui-state-default " val="' . $i . '">' . $i . '</a>';
                }
            }

            $paginate .= '</span>';

            if($quickjump){
                $paginate .= '<a class="setpage ui-button ui-state-default tip-top" title="点击输入页码，按回车快速跳转"><label>第 <input type="text" value="'.$page.'" id="custom_page" /> 页<span title="共'.$this->countpage.'页"> / 共 '.$this->countpage.' 页</span></label></a>';
                $paginate .= '<a class="setlimit ui-button ui-state-default tip-top" title="点击输入每页条数，按回车快速跳转"><label>每页 <input type="text" value="'.$this->limit.'" id="custom_limit" /> 条<span title="共'.$this->count.'条"> / 共 '.$this->count.' 条</span></label></a>';
            }

            if($page == $this->countpage){
                $paginate .= '<a class="next fg-button ui-button ui-state-default ui-state-disabled" id="next_page" title="下一页">下一页</a>';
                $paginate .= '<a class="last ui-corner-tr ui-corner-br fg-button ui-button ui-state-default ui-state-disabled" id="last_page" title="最后一页">尾页</a>';
            }else{
                $paginate .= '<a class="next fg-button ui-button ui-state-default" id="next_page" title="下一页">下一页</a>';
                $paginate .= '<a class="last ui-corner-tr ui-corner-br fg-button ui-button ui-state-default" id="last_page" title="最后一页">尾页</a>';
            }
            $paginate .= '<input type="hidden" id="page_param" countpage="' . $this->countpage . '" page="' . $page . '" />';
            $paginate .= '</div>';
            $paginate .= '</div>';
        }
        return $paginate;
    }

    /**
     * 获取页码信息
     * @return array
     */
    public function getPageInfo() {
        return array(
            'recordTotal' => intval($this->count),      // 总条数
            'pageTotal'   => intval($this->countpage),  // 总页数
            'pageNo'      => intval($this->page),       // 当前页码
            'pageLength'  => intval($this->limit)       // 每页条数
        );
    }

    /**
     * 将key加密后输出到前台
     */
    public function key($key){
        return base64_encode(authcode($key,'ENCODE'));
    }
}
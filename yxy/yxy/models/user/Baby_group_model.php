<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once( APPPATH . 'models/Lc_model.php' );

class Baby_group_model extends Lc_model {

    /**
     * 构造方法，设置默认表名
     */
    public function __construct() {
        parent::__construct();
        $this->table_name = 'baby_group';
    }
}
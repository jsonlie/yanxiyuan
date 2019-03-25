<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LC_Input extends CI_Input{

    public function __construct(){
        parent::__construct();
    }

    /**
     * Is ajax Request?
     *
     * Test to see if a request contains the HTTP_X_REQUESTED_WITH header
     *
     * @return  boolean
     */
    public function is_wechat_request(){
        return strpos($this->server('HTTP_USER_AGENT'), 'MicroMessenger');
    }
}
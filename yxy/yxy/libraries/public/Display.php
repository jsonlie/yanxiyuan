<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author cat<lynxcatdeng@gmail.com>
 * @create 2015-3-26
 * @description 展示层
 */
class Display {
    private $CI;

    public function __construct() {
        $this->CI = &get_instance();
    }

    public function view($config, $data = array()){
        if (isset($config['display'])) {
            $fun = $config['display'];
        } else {
            $fun = $this->CI->input->get('display');

            if (empty($fun) || ! method_exists($this, 'display' . $fun)) {
                $fun = 'default';
            }

            $config['display'] = $fun;
        }

        $fun = 'display'.$fun;

        if ('excel' == $config['display']) {
            $this->CI->config->load('excel');
            $excelConfig = $this->CI->config->item($config['excel']);

            if (! empty($excelConfig['format'])) {
                $config['data'] = $excelConfig['data'];
                $config['format'] = $excelConfig['format'];
                $data = $this->handleData($config, $data);
            }
        } else {
            if(isset($config['dataHandleRules'])){
                $data = $this->handleData($config, $data);
            }

            if(isset($config['dataHeader'])){
                $data['dataHeader'] = $config['dataHeader'];
            }

            if(isset($config['dataSearch'])){
                $data['dataSearch'] = $config['dataSearch'];
            }
        }

        $this->$fun($config, $data);
    }


    /**
     * 处理数据
     */
    private function handleData($config, $data){
        $this->CI->load->library(array('utils/dataformat' => 'format'));
        $result = array();

        if ('excel' == $config['display']) {
            $keys = array();

            foreach ($config['format'] as $key => $val) {
                $keys[] = current(array_keys($config['data'], $key));
            }

            foreach ($data['data'] as $row => $rowData) {
                foreach ($rowData as $col => $colData) {
                    if (isset($config['format'][$col])) {
                        $data['data'][$row][$col] = $this->CI->format->col($colData, $config['format'][$col]);
                    }
                }
            }
        } else {
            $keys = array_keys($config['dataHeader']);
            $rules = $config['dataHandleRules'];

            for($i = 0, $len = count($data['data']); $i < $len; $i++){
                array_push($result, $this->CI->format->row($data['data'][$i], $keys, $rules));
            }

            $data['data'] = $result;
        }

        return $data;
    }


    private function displayJson($config, $data){
        $this->CI->output->set_content_type('application/json;charset=utf-8');
        echo json_encode($data);
    }

    private function displayJsonp($config, $data){
        $this->CI->output->set_content_type('application/json;charset=utf-8');
        $callback = $this->CI->input->get('callback');
        echo $callback . '(' . json_encode($data) . ')';
    }

    /**
     * 输出excel
     */
    private function displayExcel(array $config, array $data) {
        $this->CI->load->library('utils/excel');
        $this->CI->excel->output($config['excel'], $data['data']);
    }

    private function displayText(){

    }

    private function displayDefault($config, $data){
        $this->CI->load->view($config['path'], $data);
    }

}
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 扩展loader类，支持CSS,JS，图片的加载
 */

class LC_Loader extends CI_Loader{

    public function __construct(){
        parent::__construct();
    }

    private $statics = array(
        'css' => array(),
        'js' => array()
    );

    /**
     * 返回静态文件列表
     */
    public function staticfile(){
        //TODO， 文件合并
        $statics = array(
            'css' => implode('', $this->statics['css']),
            'js' => implode('', $this->statics['js'])
        );

        return $statics;
    }

    /**
     * 加载资源，html格式输出
     * @param string $path 路径
     * @param string $type 类型:css,js或image
     */
    private function statics($path, $type){
        if($type == 'css'){
            array_push($this->statics['css'], '<link rel="stylesheet" type="text/css" href="' . base_url($path) . '?v=' . STATIC_VERSION . '">');
        }else if($type == 'js'){
            array_push($this->statics['js'], '<script type="text/javascript" src="' . base_url($path) . '?v=' . STATIC_VERSION . '"></script>');
        }else if($type == 'image'){
            echo '<img src="' . base_url($path) . '?v=' . STATIC_VERSION . '" />' . "\n";
        }
    }

    /**
     * 加载CSS文件，开发状态加载未经压缩的文件，生产状态则加载压缩的文件
     * @param String $filename 文件名
     * @param String $type     类型，指定文件是前台还是后台，默认前台
     */
    public function css($filename, $type = 'front'){
        $path = '';

        if($filename == ''){
            return 'test';
        }

        if($type == 'front'){
            $path .= (ENVIRONMENT === 'production') ? FRONT_COMPRESS_STYLE_DIR : FRONT_STYLE_DIR;
        }else if($type == 'admin'){
            $path .= (ENVIRONMENT === 'production') ? ADMIN_COMPRESS_STYLE_DIR : ADMIN_STYLE_DIR;
        }

        if(ENVIRONMENT === 'production'){
            $filename = str_replace('.css', ".min.css", $filename);
        }

        $this->statics($path . $filename, 'css');
    }

    /**
     * 加载JS文件，开发状态加载未经压缩的文件，生产状态则加载压缩的文件
     * @param String $filename 文件名
     * @param String $type     类型，指定文件是前台还是后台，默认前台
     */
    public function js($filename, $type = 'front'){
        $path = '';

        if($filename == ''){
            return 'test1';
        }

        if($type == 'front'){
            $path .= (ENVIRONMENT === 'production') ? FRONT_COMPRESS_SCRIPT_DIR : FRONT_SCRIPT_DIR;
        }else if($type == 'admin'){
            $path .= (ENVIRONMENT === 'production') ? ADMIN_COMPRESS_SCRIPT_DIR : ADMIN_SCRIPT_DIR;
        }

        if(ENVIRONMENT === 'production'){
            $filename = str_replace('.js', ".min.js", $filename);
        }

        $this->statics($path . $filename, 'js');
    }

    /**
     * 加载图片
     * @param String $filename 文件名
     * @param string $type     类型，指定文件是common或者library文件下，默认空字符串
     */
    function img($filename, $type = 'front'){
        $path = '';

        if($type == 'front'){
            $path .= FRONT_IMAGE_DIR;
        }else if($type == 'admin'){
            $path .= ADMIN_IMAGE_DIR;
        }

        $this->statics($path . $filename, 'image');
    }
}
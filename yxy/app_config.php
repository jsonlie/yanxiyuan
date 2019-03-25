<?php

    /**
    * 本地配置文件
    * @author cat <lynxcatdeng@gmail.com>
    * @create 2013-09-28
    */
    define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');

    /*
     *---------------------------------------------------------------
     * ERROR REPORTING
     *---------------------------------------------------------------
     *
     * Different environments will require different levels of error reporting.
     * By default development will show errors but testing and live will hide them.
     */
    switch (ENVIRONMENT)
    {
        case 'development': 
		#error_reporting(-1);
            #ini_set('display_errors', 1);
            ini_set('display_errors', 0);
            if (version_compare(PHP_VERSION, '5.3', '>='))
            {
                error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
            }
            else
            {
                error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
            }
        break;

        case 'testing':
        case 'production':
            ini_set('display_errors', 0);
            if (version_compare(PHP_VERSION, '5.3', '>='))
            {
                error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
            }
            else
            {
                error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
            }
        break;

        default:
            header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
            echo 'The application environment is not set correctly.';
            exit(1); // EXIT_ERROR
    }

    /*
     *---------------------------------------------------------------
     * SYSTEM FOLDER NAME
     *---------------------------------------------------------------
     *
     * This variable must contain the name of your "system" folder.
     * Include the path if the folder is not in the same directory
     * as this file.
     */

    $system_path = (ENVIRONMENT == 'production') ? '/data/vhost/web/CodeIgniter/system/' : '/data/vhost/web/CodeIgniter/system/';

    /*
     *---------------------------------------------------------------
     * APPLICATION FOLDER NAME
     *---------------------------------------------------------------
     *
     * If you want this front controller to use a different "application"
     * folder than the default one you can set its name here. The folder
     * can also be renamed or relocated anywhere on your server. If
     * you do, use a full server path. For more info please see the user guide:
     * http://codeigniter.com/user_guide/general/managing_apps.html
     *
     * NO TRAILING SLASH!
     */
    $application_folder = getcwd();
?>
